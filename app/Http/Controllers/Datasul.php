<?php

namespace App\Http\Controllers;

use Hennig\Common\AllowGuest;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Datasul
{
    use AllowGuest;

    public function convert($base64_content)
    {
        [, $content] = explode(',', $base64_content);
        $lines = preg_split("/\r\n|\n|\r/", base64_decode($content));
        $header = [['Matrícula', 'Nome', 'Admissão', 'Início', 'Término', 'Concessão', 'Dir', 'Conc', 'Saldo']];
        foreach ($lines as $line) {
            $line = utf8_encode($line);
            if (preg_match('/-----|MERCUR S.A|Estabeleci|Matrícula|\x0c/', $line)) continue;

            if (!$line) continue;
            $matricula = trim(substr($line, 0, 10));
            $nome = trim(substr($line, 12, 24));
            $adm = trim(substr($line, 37, 10));
            $periodo_inicial = trim(substr($line, 48, 10));
            $periodo_final = trim(substr($line, 59, 10));
            $concessao = trim(substr($line, 70, 10));
            $dir = trim(substr($line, 81, 4));
            $conc = trim(substr($line, 86, 6));
            $saldo = trim(substr($line, 93, 4));

            if (!$matricula &&
                !$nome &&
                !$adm &&
                !$periodo_inicial &&
                !$periodo_final &&
                !$concessao &&
                !$dir &&
                !$conc &&
                !$saldo
            ) continue;

            $header[] = [
                $matricula, $nome, $adm, $periodo_inicial, $periodo_final, $concessao, $dir, $conc, $saldo
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($header);

        $writer = new Xlsx($spreadsheet);
        $file = 'relatorio-'.uniqid().'.xlsx';
        $path = storage_path("app/$file");
        $writer->save($path);
        return config('app.url') . "download/$file";
    }
}
