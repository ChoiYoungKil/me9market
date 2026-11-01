<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminListExport
{
    public function download($query, string $name, array $columns)
    {
        if ((clone $query)->count() > 5000) {
            throw ValidationException::withMessages(['export' => '검색 조건을 좁혀 5,000건 이하로 다운로드해 주세요.']);
        }
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->setTitle($name);
        $sheet->fromArray(array_keys($columns), null, 'A1');
        $line = 2;
        foreach ($query->get() as $record) {
            $index = 1;
            foreach ($columns as $value) {
                $cell = is_callable($value) ? $value($record) : data_get($record, $value);
                $sheet->setCellValueExplicit([$index++, $line], (string) ($cell ?? ''), DataType::TYPE_STRING);
            }
            $line++;
        }
        $sheet->freezePane('A2');
        $last = Coordinate::stringFromColumnIndex(count($columns));
        $sheet->setAutoFilter('A1:'.$last.max(1, $line - 1));
        $sheet->getStyle('A1:'.$last.'1')->getFont()->setBold(true);
        foreach (range(1, count($columns)) as $i) {
            $sheet->getColumnDimensionByColumn($i)->setWidth(24);
        }

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
            $book->disconnectWorksheets();
        }, $name.'-'.now()->format('Ymd-His').'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'private, no-store']);
    }
}
