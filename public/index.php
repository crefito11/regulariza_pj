<?php

ob_end_clean();
ob_implicit_flush(true);
ob_start();

ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);

header('Cache-Control: no-cache');
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';

use Src\Service\LoteCnpjService;

?>

<!DOCTYPE html>
<html>

<head>
    <title>Consulta em Lote CNPJ</title>
</head>

<body>

    <h2>Consulta de CNPJs em Lote</h2>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="planilha" accept=".xlsx,.xls,.csv" required>
        <button type="submit">Processar</button>
    </form>

    <hr>

    <div style="width:100%; background:#ddd; border-radius:5px; margin-top:20px;">
        <div id="progress-bar"
            style="width:0%; height:30px; background:#28a745;
                    color:white; text-align:center; line-height:30px;
                    border-radius:5px;">
            0%
        </div>
    </div>

    <?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_FILES['planilha']) && $_FILES['planilha']['error'] === 0) {

            $caminhoTemp = $_FILES['planilha']['tmp_name'];

            $service = new LoteCnpjService();
            $resultados = $service->processarPlanilha($caminhoTemp);

            echo "<h3>Resultados:</h3>";
            echo "<pre>";
            print_r($resultados);
            echo "</pre>";
        }
    }

    ?>

</body>

</html>