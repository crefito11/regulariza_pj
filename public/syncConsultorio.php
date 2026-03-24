<?php

use Src\Entity\DadosProfTemp;
use Src\Service\ConsultorioService;
use Src\Service\DadosProfTempService;

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

    $profissionaisTempService = new DadosProfTempService();
    $consultorioService = new ConsultorioService();

    $listaDadosConsultorio = $consultorioService->listar();

    $total = count($listaDadosConsultorio);
    $contador = 0;

    foreach ($listaDadosConsultorio as $hash => $consultorio) {
        ++$contador;

        $nome = $consultorio->nome ?? null;

        if (empty($nome)) {
            echo "[{$contador}/{$total}] Consultório inválido.\n";
            continue;
        }

        try {
            $profTemp = $profissionaisTempService->buscarPorNome($nome);

            if (!($profTemp instanceof DadosProfTemp)) {
                echo "[{$contador}/{$total}] {$nome} não encontrado.\n";
                continue;
            }

            $consultorio->cpf = $profTemp->cpf ?? $consultorio->cpf;
            $consultorio->area_atuacao = $profTemp->categoria === 'FISIOTERAPEUTA' ? 'FISIOTERAPIA' : 'TERAPIA OCUPACIONAL';

            $consultorioService->atualizar($consultorio);

            echo "[{$contador}/{$total}] {$nome} atualizado.\n";
        } catch (Throwable $e) {
            echo "[{$contador}/{$total}] ERRO {$nome}: {$e->getMessage()}\n";
        }
    }

    echo "Finalizado. {$contador} de {$total} processados.\n";
}

main();
