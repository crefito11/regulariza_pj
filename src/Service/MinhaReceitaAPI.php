<?php

namespace Src\Service;

class MinhaReceitaAPI
{
    private string $baseUrl = "https://minhareceita.org/";

    public function consultarCnpj(string $cnpj): ?array
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        $url = $this->baseUrl . $cnpj;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return null;
        }

        return json_decode($response, true);
    }
}
