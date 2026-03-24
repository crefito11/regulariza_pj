<?php

namespace Src\Service;

use Src\Config\Database;
use Src\Entity\DadosProfTemp;

class DadosProfTempService
{
    private \PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function criar(DadosProfTemp $dadosTemp): int
    {
        $sql = 'INSERT INTO profissionais_temp (
            nome, cpf, categoria
        ) VALUES (
            :nome, :cpf, :categoria
        )';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($this->mapearParametros($dadosTemp));

        return (int) $this->conn->lastInsertId();
    }

    public function buscarPorNome(string $nome): ?DadosProfTemp
    {
        $sql = 'SELECT * FROM profissionais_temp WHERE nome = :nome';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['nome' => $nome]);

        $resultado = $stmt->fetchObject(DadosProfTemp::class);

        return $resultado ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM profissionais_temp WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function listar(): array
    {
        $sql = 'SELECT * FROM profissionais_temp ORDER BY nome ASC';

        return $this->conn->query($sql)->fetchAll();
    }

    private function mapearParametros(DadosProfTemp $dadosTemp): array
    {
        return [
            'nome' => $dadosTemp->nome,
            'cpf' => $dadosTemp->cpf,
            'categoria' => $dadosTemp->categoria,
        ];
    }
}
