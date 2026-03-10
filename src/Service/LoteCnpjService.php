<?php

namespace Src\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Src\Entity\Empresa as EntityEmpresa;
use Src\Entity\Log;
use Src\Entity\Relatorio;

class LoteCnpjService
{
    private MinhaReceitaAPI $api;
    private RelatorioService $relatorioService;
    private LogService $logService;
    private EmpresaService $empresaService;
    private array $resultados = [];

    public function __construct()
    {
        $this->api = new MinhaReceitaAPI();
        $this->relatorioService = new RelatorioService();
        $this->logService = new LogService();
        $this->empresaService = new EmpresaService();
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

    private function extrairCnpjsValidos($sheet): array
    {
        $cnpjs = [];

        foreach ($sheet->getRowIterator() as $row) {
            $linha = $row->getRowIndex();

            if ($linha === 1) {
                continue;
            }

            $valor = (string) ($sheet->getCell('A'.$linha)->getValue() ?? '');
            $cnpj = preg_replace('/\D/', '', trim($valor));

            if (strlen($cnpj) === 14) {
                $cnpjs[] = $cnpj;
            }
        }

        return $cnpjs;
    }

    private function montarEndereco(array $dados): ?string
    {
        $logradouro = $dados['logradouro'] ?? null;
        $numero = $dados['numero'] ?? null;
        $complemento = $dados['complemento'] ?? null;
        $municipio = $dados['municipio'] ?? null;
        $bairro = $dados['bairro'] ?? null;
        $cep = $dados['cep'] ?? null;

        $partes = [];

        // Logradouro + número
        if ($logradouro) {
            $endereco = $logradouro;

            if ($numero) {
                $endereco .= ' '.$numero;
            }

            $partes[] = $endereco;
        }

        // Complemento
        if ($complemento) {
            $partes[] = $complemento;
        }

        // Cidade - bairro
        if ($municipio || $bairro) {
            $cidadeBairro = trim(($municipio ?? '').' - '.($bairro ?? ''), ' -');
            $partes[] = $cidadeBairro;
        }

        // CEP
        if ($cep) {
            $partes[] = 'CEP: '.$cep;
        }

        return !empty($partes) ? implode(', ', $partes) : null;
    }

    private function isSluOuEi(array $dados): bool
    {
        $natureza = $dados['codigo_natureza_juridica'] ?? null;
        $qsa = $dados['qsa'] ?? [];

        // Empresário Individual
        if ($natureza == '2135') {
            return true;
        }

        // Sociedade Limitada Unipessoal (SLU)
        if ($natureza == '2062' && count($qsa) <= 1) {
            return true;
        }

        return false;
    }

    private function criarEmpresaAPartirApi(array $dados): EntityEmpresa
    {
        $empresa = new EntityEmpresa();

        $empresa->cnpj = $this->formatarCnpj($dados['cnpj']);
        $empresa->razao_social = $dados['razao_social'];
        $empresa->nome_fantasia = $dados['nome_fantasia'] ?? null;
        $empresa->natureza_juridica = $dados['natureza_juridica'] ?? null;
        $empresa->slu_ei = $this->isSluOuEi($dados);
        $empresa->atividade = $dados['cnae_fiscal_descricao'] ?? null;
        $empresa->cnae_principal = $dados['cnae_fiscal'] ?? null;
        $empresa->inscricao_estadual = null;
        $empresa->endereco = $this->montarEndereco($dados);
        $empresa->cidade = $dados['municipio'] ?? null;
        $empresa->email = $dados['email'] ?? null;
        $empresa->telefone = $dados['ddd_telefone_1'] ?? null;
        $empresa->uf = $dados['uf'] ?? null;
        $empresa->situacao_cadastral = $dados['descricao_situacao_cadastral'] ?? null;
        $empresa->descricao_matriz_filial = $dados['descricao_identificador_matriz_filial'] ?? null;

        return $empresa;
    }

    private function registrarFalha(int $idRelatorio, string $mensagem): void
    {
        $log = new Log();
        $log->id_relatorio = $idRelatorio;
        $log->mensagem = $mensagem;

        $this->logService->criar($log);
    }

    private function finalizarRelatorio(int $idRelatorio, int $processados, int $falhas): void
    {
        $relatorio = $this->relatorioService->buscarPorId($idRelatorio);

        $relatorio->qtdItemsProcessados = $processados;
        $relatorio->qtdFalhas = $falhas;

        $this->relatorioService->atualizar($relatorio);
    }

    public function processarPlanilha(string $caminhoArquivo, string $nome_relatorio): array
    {
        $spreadsheet = IOFactory::load($caminhoArquivo);
        $sheet = $spreadsheet->getActiveSheet();

        $cnpjs = $this->extrairCnpjsValidos($sheet);

        $total = count($cnpjs);
        $contador = 0;
        $falhas = 0;

        // Criar relatório
        $relatorio = new Relatorio();
        $relatorio->nome = $nome_relatorio;
        $relatorio->qtdItemsProcessados = 0;
        $relatorio->qtdFalhas = 0;

        $idRelatorio = $this->relatorioService->criar($relatorio);

        foreach ($cnpjs as $cnpj) {
            $dados = $this->api->consultarCnpj($cnpj);

            if (!$dados) {
                continue;
            }

            if (!empty($dados['message'])) {
                $this->registrarFalha($idRelatorio, $dados['message']);
                ++$falhas;
            } else {
                $empresa = $this->criarEmpresaAPartirApi($dados);
                $this->empresaService->salvarOuAtualizar($empresa);
            }

            ++$contador;

            $this->atualizarProgresso($contador, $total);

            sleep(5);

            // a cada 10 itens, aguarda 1 minuto
            if ($contador % 10 === 0) {
                echo "Aguardando 60 segundos para evitar bloqueio da API...\n";
                sleep(60);
            }
        }

        $this->finalizarRelatorio($idRelatorio, $contador, $falhas);

        return [
            [
                'total' => $total,
                'processados' => $contador,
                'falhas' => $falhas,
            ],
        ];
    }
}
