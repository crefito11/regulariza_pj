<?php

use Src\Service\EmpresaService;

require_once __DIR__.'/../vendor/autoload.php';

function main()
{
    $empresaService = new EmpresaService();
    $listaEmpresasBaixadas = $empresaService->listaBaixadas();

    $registros = [];

    foreach ($listaEmpresasBaixadas as $empresa) {
        if (!empty($empresa['numero_inscricao'])) {
            $registros[] = $empresa['numero_inscricao'];
        }
    }

    $resultado = implode(';', $registros);

    echo $resultado;
}

main();
