<?php

namespace Src\Service;

use PDO;
use Src\Config\Database;
use Src\Entity\Log;

class LogService
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function criar(Log $log): int
    {
        $sql = "INSERT INTO log (
            id_relatorio, mensagem
        ) VALUES (
            :id_relatorio, :mensagem
        )";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($this->mapearParametros($log));

        return (int)$this->conn->lastInsertId();
    }

    /* public function atualizar(Empresa $empresa): bool
    {
        $sql = "UPDATE empresa SET
            razao_social = :razao_social,
            nome_fantasia = :nome_fantasia,
            natureza_juridica = :natureza_juridica,
            slu_ei = :slu_ei,
            atividade = :atividade,
            cnae_principal = :cnae_principal,
            inscricao_estadual = :inscricao_estadual,
            endereco = :endereco,
            cidade = :cidade,
            email = :email,
            telefone = :telefone,
            uf = :uf,
            situacao_cadastral = :situacao_cadastral,
            descricao_matriz_filial = :descricao_matriz_filial
        WHERE cnpj = :cnpj";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($this->mapearParametros($empresa));
    } */

    /* public function buscarPorCnpj(string $cnpj): ?array
    {
        $sql = "SELECT * FROM empresa WHERE cnpj = :cnpj";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['cnpj' => $cnpj]);

        $resultado = $stmt->fetch();
        return $resultado ?: null;
    } */

    public function buscarPorIdRelatorio(int $id): ?array
    {
        $sql = "SELECT * FROM log WHERE id_relatorio = :id";
        $stmt = $this->conn->prepare(query: $sql);
        $stmt->execute(['id_relatorio' => $id]);

        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function listar(): array
    {
        $sql = "SELECT * FROM log ORDER BY created_at ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    /* public function salvarOuAtualizar(Empresa $empresa): void
    {
        $existe = $this->buscarPorCnpj($empresa->cnpj);

        if ($existe) {
            $this->atualizar($empresa);
        } else {
            $this->criar($empresa);
        }
    } */

    private function mapearParametros(Log $log): array
    {
        return [
            'id_relatorio' => $log->id_relatorio,
            'mensagem' => $log->mensagem
        ];
    }
}
