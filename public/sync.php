<?php

use Src\Entity\Empresa;
use Src\Entity\RegistroCrefito;
use Src\Service\DadosTempService;
use Src\Service\EmpresaService;
use Src\Service\RegistroCrefitoService;

require_once __DIR__.'/../vendor/autoload.php';

function converterDataParaBR(?string $data): ?string
{
    if (empty($data)) {
        return null;
    }

    $date = DateTime::createFromFormat('n/j/Y', $data);

    if (!$date) {
        return null; // ou pode lançar exceção se quiser
    }

    return $date->format('j/n/Y');
}
function main()
{
    echo "Começando o script...\n";

    $dadosTempService = new DadosTempService();
    $empresaService = new EmpresaService();
    $registroCrefitoService = new RegistroCrefitoService();

    $listaDadosTemp = $dadosTempService->listar();

    $total = count($listaDadosTemp);
    $contador = 0;

    foreach ($listaDadosTemp as $dadoTemp) {
        ++$contador;
        $cnpj = $dadoTemp['cnpj'] ?? null;

        if (empty($cnpj)) {
            echo "[{$contador}/{$total}] CNPJ inválido ou vazio.\n";
            continue;
        }

        try {
            $empresa = $empresaService->buscarPorCnpjOb($cnpj);

            if (!($empresa instanceof Empresa)) {
                echo "[{$contador}/{$total}] CNPJ {$cnpj} não encontrado.\n";
                continue;
            }

            if (!empty($empresa->id_dados_crefito)) {
                echo "[{$contador}/{$total}] CNPJ {$cnpj} já possui dados de registro.\n";
                continue;
            }

            $registro = new RegistroCrefito();
            $registro->registro = $dadoTemp['registro'] ?? null;
            $registro->dt_registro = converterDataParaBR($dadoTemp['dt_registro']) ?? null;
            $registro->servicos = $dadoTemp['servicos'] ?? null;
            $registro->qtde_rt = $dadoTemp['qtde_rt'] ?? null;

            $idRegistro = $registroCrefitoService->criar($registro);

            // mantém dados existentes e só altera o necessário
            $empresa->id_dados_crefito = $idRegistro;
            $empresa->email = $dadoTemp['email'] ?? $empresa->email;
            $empresa->inscricao_estadual = $dadoTemp['inscricao_estadual'] ?? $empresa->inscricao_estadual;

            $empresaService->atualizar($empresa);

            echo "[{$contador}/{$total}] CNPJ {$cnpj} atualizado com sucesso.\n";
        } catch (Throwable $e) {
            echo "[{$contador}/{$total}] ERRO no CNPJ {$cnpj}: {$e->getMessage()}\n";
        }
    }

    echo "Finalizado. {$contador} de {$total} processados.\n";
}

main();
