<?php

namespace Src\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class LoteCnpjService
{
    protected MinhaReceitaAPI $api;
    private array $resultados = [];

    public function __construct()
    {
        $this->api = new MinhaReceitaAPI();
    }

    private function atualizarProgresso(int $atual, int $total): void
    {
        $percentual = intval(($atual / $total) * 100);

        echo "<script>
                document.getElementById('progress-bar').style.width = '{$percentual}%';
                document.getElementById('progress-bar').innerHTML = '{$percentual}%';
            </script>";

        echo str_repeat(' ', 1024); // força envio imediato
        ob_flush();
        flush();
    }

    public function processarPlanilha(string $caminhoArquivo): array
    {
        $spreadsheet = IOFactory::load($caminhoArquivo);
        $sheet = $spreadsheet->getActiveSheet();

        $linhasValidas = [];

        // 🔎 Primeiro: coletar apenas CNPJs válidos
        foreach ($sheet->getRowIterator() as $row) {

            $linha = $row->getRowIndex();

            if ($linha === 1) {
                continue;
            }

            $valor = (string) ($sheet->getCell('A' . $linha)->getValue() ?? '');
            $cnpj = preg_replace('/\D/', '', trim($valor));

            if ($cnpj !== '' && strlen($cnpj) === 14) {
                $linhasValidas[] = $cnpj;
            }
        }

        $total = count($linhasValidas);
        $contador = 0;

        // 🚀 Agora processa apenas os válidos
        foreach ($linhasValidas as $cnpj) {

            $dados = $this->api->consultarCnpj($cnpj);

            $this->resultados[] = [
                'cnpj' => $cnpj,
                'dados' => $dados
            ];

            $contador++;

            $this->atualizarProgresso($contador, $total);

            sleep(5);
        }

        return $this->resultados;
    }
}
