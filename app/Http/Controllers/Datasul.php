<?php

namespace App\Http\Controllers;

use Hennig\Common\AllowGuest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Datasul
{
    use AllowGuest;

    public function convertPE5410($base64_content)
    {
        $lines = $this->getLinesFrom($base64_content);
        $data = [['Matrícula', 'Nome', 'Saldo', 'Situação']];
        foreach ($lines as $line) {
            $line = utf8_encode($line);
            if (preg_match('/-----|MERCUR S.A|Estabeleci|BANCO DE HORAS|Contrato :|Matrícula|Totalização|\x0c/', $line)) continue;
            if (preg_match('/Positivas:|Negativas:|Compensadas:|Folha:|Pagar:|Descontar:|Suspensas:|Pagas:|Descontadas:|Autorizadas:|Saldo:/', $line)) continue;
            if (!$line) continue;
            $matricula = trim(substr($line, 0, 11));
            $nome = trim(substr($line, 11, 40));
            $saldo = trim(substr($line, 52, 11));
            $situacao = trim(substr($line, 64, 8));
            $saldo = preg_replace_callback('/(\d+):/', fn ($m) => (int)($m[1]) . ':'  , $saldo);

            if (!$matricula &&
                !$nome &&
                !$saldo &&
                !$situacao
            ) continue;

            $data[] = [
                $matricula, $nome, $saldo, $situacao
            ];
        }

        return $this->createXlsx($data);
    }

    public function convert($base64_content)
    {
        $lines = $this->getLinesFrom($base64_content);
        $data = [['Matrícula', 'Nome', 'Admissão', 'Início', 'Término', 'Concessão', 'Dir', 'Conc', 'Saldo']];
        foreach ($lines as $line) {
            $line = utf8_encode($line);
            if (preg_match('/-----|MERCUR S.A|Estabeleci|Matrícula|\x0c/', $line)) continue;

            if (!$line) continue;
            $tmp_matricula = trim(substr($line, 0, 10));
            if ($tmp_matricula) {
                $matricula = $tmp_matricula;
            }

            $tmp_nome = trim(substr($line, 12, 24));
            if ($tmp_nome) {
                $nome = $tmp_nome;
            }

            $tmp_adm = trim(substr($line, 37, 10));
            if ($tmp_adm) {
                $adm = $tmp_adm;
            }

            $periodo_inicial = trim(substr($line, 48, 10));
            $periodo_final = trim(substr($line, 59, 10));
            $concessao = trim(substr($line, 70, 10));
            $dir = trim(substr($line, 81, 4));
            $conc = trim(substr($line, 86, 6));
            $saldo = trim(substr($line, 93, 4));

            if (!$tmp_matricula &&
                !$tmp_nome &&
                !$tmp_adm &&
                !$periodo_inicial &&
                !$periodo_final &&
                !$concessao &&
                !$dir &&
                !$conc
            ) continue;

            $data[] = [
                $matricula, $nome, $adm, $periodo_inicial, $periodo_final, $concessao, $dir, $conc, $saldo
            ];
        }

        return $this->createXlsx($data);
    }

    /**
     * @param $base64_content
     */
    private function getLinesFrom($base64_content)
    {
        [, $content] = explode(',', $base64_content);
        return preg_split("/\r\n|\n|\r/", base64_decode($content));
    }

    /**
     * @param array $data
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function createXlsx(array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);

        $writer = new Xlsx($spreadsheet);
        $file = 'relatorio-' . uniqid() . '.xlsx';
        $path = storage_path("app/$file");
        $writer->save($path);
        return config('app.url') . "/download/$file";
    }
}
