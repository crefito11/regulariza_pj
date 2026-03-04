<?php

namespace Src\Service;

use PDO;
use Src\Config\Database;
use Src\Entity\Relatorio;

class RelatorioService
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function criar(Relatorio $relatorio): int
    {
        $sql = "INSERT INTO relatorio (
            nome, qtdItemsProcessados, qtdFalhas
        ) VALUES (
            :nome, :qtdItemsProcessados, :qtdFalhas
        )";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($this->mapearParametros($relatorio));

        return (int)$this->conn->lastInsertId();
    }

    public function atualizar(Relatorio $relatorio): bool
    {
        $sql = "UPDATE relatorio SET
            nome = :nome,
            qtdItemsProcessados = :qtdItemsProcessados,
            qtdFalhas = :qtdFalhas
        WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'nome' => $relatorio->nome,
            'qtdItemsProcessados' => $relatorio->qtdItemsProcessados,
            'qtdFalhas' => $relatorio->qtdFalhas,
            'id' => $relatorio->id
        ]);
    }

    /* public function buscarPorCnpj(string $cnpj): ?array
    {
        $sql = "SELECT * FROM empresa WHERE cnpj = :cnpj";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['cnpj' => $cnpj]);

        $resultado = $stmt->fetch();
        return $resultado ?: null;
    } */

    public function buscarPorId(int $id): ?Relatorio
    {
        $sql = "SELECT * FROM relatorio WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, Relatorio::class);
        $relatorio = $stmt->fetch();

        return $relatorio ?: null;
    }

    public function listar(): array
    {
        $sql = "SELECT * FROM relatorio ORDER BY id DESC";
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

    private function mapearParametros(Relatorio $relatorio): array
    {
        return [
            'nome' => $relatorio->nome,
            'qtdItemsProcessados' => $relatorio->qtdItemsProcessados,
            'qtdFalhas' => $relatorio->qtdFalhas
        ];
    }
}
