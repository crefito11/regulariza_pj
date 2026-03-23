<?php

use Src\Entity\Empresa;
use Src\Service\DadosTempService;
use Src\Service\EmpresaService;

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

            // mantém dados existentes e só altera o necessário
            $empresa->email = $dadoTemp['email'] ?? $empresa->email;
            $empresaService->atualizar($empresa);

            echo "[{$contador}/{$total}] E-mail do CNPJ {$cnpj} atualizado com sucesso.\n";
        } catch (Throwable $e) {
            echo "[{$contador}/{$total}] ERRO no CNPJ {$cnpj}: {$e->getMessage()}\n";
        }
    }

    echo "Finalizado. {$contador} de {$total} processados.\n";
}

main();
