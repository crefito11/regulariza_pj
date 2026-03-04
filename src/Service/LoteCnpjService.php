<?php

namespace Src\Service;

use Empresa;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Src\Entity\Log;
use Src\Entity\Relatorio;

class LoteCnpjService
{
    private MinhaReceitaAPI $api;
    private RelatorioService $relatorioService;
    private LogService $logService;
    private array $resultados = [];

    public function __construct()
    {
        $this->api = new MinhaReceitaAPI();
        $this->relatorioService = new RelatorioService();
        $this->logService = new LogService();
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

    private function formatarCnpj(string $cnpj): string
    {
        // Remove tudo que não for número
        $cnpj = preg_replace('/\D/', '', $cnpj);

        // Garante que tenha 14 dígitos
        $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);

        // Aplica a máscara
        return preg_replace(
            '/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/',
            '$1.$2.$3/$4-$5',
            $cnpj
        );
    }

    public function processarPlanilha(string $caminhoArquivo, string $nome_relatorio): array
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
        $falhas = 0;

        // Iniciar relatorio
        $relatorio = new Relatorio();
        $relatorio->nome = $nome_relatorio;
        $relatorio->qtdItemsProcessados = $contador;
        $relatorio->qtdFalhas = $falhas;
        $id_relatorio = $this->relatorioService->criar(relatorio: $relatorio);

        // 🚀 Agora processa apenas os válidos
        foreach ($linhasValidas as $cnpj) {

            $dados = $this->api->consultarCnpj($cnpj);

            if (!$dados) continue;

            if (!empty($dados['message'])) {
                $log = new Log();
                $log->id_relatorio = $id_relatorio;
                $log->mensagem = $dados['message'];
                $this->logService->criar($log);
                $falhas++;
            }/*  else {
                $empresa = new Empresa();
                $empresa->cnpj = $dados['cnpj'];
                $empresa->razao_social = $dados['razao_social'];
                $empresa->nome_fantasia = $dados['nome_fantasia'] ?? null;
                $empresa->natureza_juridica = $dados['natureza_juridica'];
                $empresa->slu_ei = false;
                $empresa->atividade = $dados['atividade_principal'][0]['text'] ?? '';
                $empresa->cnae_principal = $dados['atividade_principal'][0]['code'] ?? '';
                $empresa->inscricao_estadual = '';
                $empresa->endereco = $dados['logradouro'] . ', ' . $dados['numero'];
                $empresa->cidade = $dados['municipio'];
                $empresa->email = $dados['email'] ?? '';
                $empresa->telefone = $dados['telefone'] ?? '';
                $empresa->uf = $dados['uf'];
                $empresa->situacao_cadastral = $dados['situacao'];
                $empresa->descricao_matriz_filial = $dados['tipo'];
            } */

            $contador++;

            $this->atualizarProgresso($contador, $total);

            sleep(5);
        }

        $obRelatorio = $this->relatorioService->buscarPorId($id_relatorio);
        $this->relatorioService->atualizar($obRelatorio);

        $this->resultados[] = [
            'processados' => $contador,
            'falhas' => $falhas,
            'dados' => $dados
        ];

        return $this->resultados;
    }
}
