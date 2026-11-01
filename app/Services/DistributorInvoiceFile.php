<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class DistributorInvoiceFile
{
    public const MAX_ROWS = 2000;

    /** Read literal values only. A bad row must never silently ship a different order. */
    public function read(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            $this->fail('CSV 또는 XLSX 파일만 업로드할 수 있습니다.');
        }
        if ($extension === 'xlsx') {
            $zip = new \ZipArchive;
            if ($zip->open($file->getRealPath()) !== true) {
                $this->fail('올바른 XLSX 파일이 아닙니다.');
            }
            $size = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $size += $zip->statIndex($i)['size'];
            }
            $zip->close();
            if ($size > 25 * 1024 * 1024) {
                $this->fail('압축 해제 용량이 너무 큽니다. 파일을 나누어 올려 주세요.');
            }
        }
        try {
            $reader = $extension === 'xlsx' ? new Xlsx : new Csv;
            $reader->setReadDataOnly(true);
            $info = $reader->listWorksheetInfo($file->getRealPath());
            if (empty($info) || $info[0]['totalRows'] > self::MAX_ROWS + 1 || $info[0]['totalColumns'] > 30) {
                $this->fail('한 파일에는 최대 2,000행, 30열까지 입력할 수 있습니다.');
            }
            if ($extension === 'xlsx') {
                $reader->setLoadSheetsOnly($info[0]['worksheetName']);
            }
            $book = $reader->load($file->getRealPath());
            $sheet = $book->getSheet(0);
            $rows = $sheet->toArray(null, false, false, false);
            $book->disconnectWorksheets();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->fail('파일을 읽을 수 없습니다. 다운로드한 양식에 송장 정보를 입력해 주세요.');
        }
        $headers = array_map(fn ($v) => strtolower(preg_replace('/[\s_\-\x{FEFF}]+/u', '', (string) $v)), array_shift($rows) ?? []);
        $aliases = [
            'id' => ['orderitemid', 'itemid', 'id', '주문상품id', '품목id'],
            'courier' => ['couriername', 'courier', '택배사'],
            'tracking' => ['trackingnumber', 'trackingno', 'invoiceno', '송장번호', '운송장번호'],
        ];
        $columns = [];
        foreach ($aliases as $key => $names) {
            $matches = array_keys(array_filter($headers, fn ($h) => in_array($h, $names, true)));
            if (count($matches) !== 1) {
                $this->fail('주문상품ID, 택배사, 송장번호 열이 각각 하나씩 있어야 합니다.');
            }
            $columns[$key] = $matches[0];
        }
        $result = [];
        $ids = [];
        foreach ($rows as $index => $row) {
            if (! array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== '')) {
                continue;
            }
            $line = $index + 2;
            $id = trim((string) ($row[$columns['id']] ?? ''));
            $courier = trim((string) ($row[$columns['courier']] ?? ''));
            $tracking = trim((string) ($row[$columns['tracking']] ?? ''));
            if (! ctype_digit($id) || (int) $id < 1 || $courier === '' || $tracking === '' || mb_strlen($courier) > 100 || mb_strlen($tracking) > 100 || (preg_match('/^[=+@\-]|[\r\n\x00-\x1F]/u', $courier) || preg_match('/^[=+@\-]|[\r\n\x00-\x1F]/u', $tracking))) {
                $this->fail($line.'행: 주문상품ID·택배사·송장번호를 확인해 주세요. 수식은 사용할 수 없습니다.');
            }
            if (isset($ids[(int) $id])) {
                $this->fail($line.'행: 같은 주문상품ID가 중복되었습니다.');
            }
            $ids[(int) $id] = true;
            $result[] = ['id' => (int) $id, 'courier' => $courier, 'tracking' => $tracking, 'line' => $line];
        }
        if (! $result) {
            $this->fail('적용할 송장 데이터가 없습니다.');
        }

        return $result;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['invoice_file' => $message.' 파일의 어느 행도 적용하지 않았습니다.']);
    }
}
