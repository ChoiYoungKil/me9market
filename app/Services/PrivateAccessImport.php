<?php

namespace App\Services;

use App\Models\ShopChannelPrivateAccess;
use Illuminate\Validation\ValidationException;

class PrivateAccessImport
{
    public function parse(string $text, string $fallback = ''): array
    {
        return $this->normalize(array_map('str_getcsv', preg_split('/\r\n|\r|\n/', $text)), $fallback);
    }

    public function file($file, string $fallback = ''): array
    {
        if (!$file) return [];
        try {
            if (in_array(strtolower($file->getClientOriginalExtension()), ['xlsx','xls'], true)) {
                $reader=\PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
                $reader->setReadDataOnly(true);
                $book=$reader->load($file->getRealPath());
                if ($book->getActiveSheet()->getHighestDataRow()>1001) throw new \RuntimeException('row limit');
                $rows=$book->getActiveSheet()->toArray(null,false,false,false);
            } else {
                $rows=[];$handle=fopen($file->getRealPath(),'r');
                while(($row=fgetcsv($handle))!==false){$rows[]=$row;if(count($rows)>1001)throw new \RuntimeException('row limit');}
                fclose($handle);
            }
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['private_access_file'=>'명단 파일을 읽을 수 없습니다. 기본양식과 1,000명 제한을 확인해 주세요.']);
        }
        return $this->normalize($rows,$fallback);
    }

    public function normalize(array $rows, string $fallback = ''): array
    {
        $result=[];
        foreach($rows as $index=>$row){
            $phone=trim((string)($row['phone']??$row[0]??''));
            if($phone==='' || in_array($phone,['휴대폰번호','핸드폰 번호','핸드폰번호','전화번호'],true))continue;
            $normalized=ShopChannelPrivateAccess::normalizePhone($phone);
            $code=trim((string)($row['entry_code']??$row[1]??$fallback));
            $userId=$row['user_id']??$row[2]??null;
            if(!preg_match('/^01[016789][0-9]{7,8}$/',$normalized) || $code==='' || mb_strlen($code)>128 || preg_match('/[\r\n]/',$code)){
                throw ValidationException::withMessages(['private_access_rows'=>($index+1).'행의 휴대폰번호와 입장코드를 확인해 주세요.']);
            }
            if(filled($userId)){
                $user=ctype_digit((string)$userId)?\App\Models\User::find($userId):null;
                if(!$user || ShopChannelPrivateAccess::normalizePhone($user->mobile)!==$normalized)throw ValidationException::withMessages(['private_access_rows'=>($index+1).'행의 회원ID와 휴대폰번호가 일치하지 않습니다.']);
            }
            $result[$normalized]=['phone'=>$phone,'phone_normalized'=>$normalized,'entry_code'=>$code,'user_id'=>filled($userId)?(int)$userId:null];
        }
        if(count($result)>1000)throw ValidationException::withMessages(['private_access_rows'=>'명단은 최대 1,000명까지 등록할 수 있습니다.']);
        return array_values($result);
    }

    public function text(array $rows): string
    {
        $quote=fn($value)=>'"'.str_replace('"','""',(string)$value).'"';
        return implode("\n",array_map(fn($r)=>implode(',',array_map($quote,[$r['phone'],$r['entry_code'],$r['user_id']??''])),$rows));
    }
}
