<?php

namespace Src\Service;

use Src\Config\Database;
use Src\Entity\RegistroCrefito;

class RegistroCrefitoService
{
    private \PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function criar(RegistroCrefito $dados): int
    {
        $sql = 'INSERT INTO dados_crefito (
            registro, dt_registro,
            servicos, qtde_rt
        ) VALUES (
            :registro, :dt_registro,
            :servicos, :qtde_rt
        )';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($this->mapearParametros($dados));

        return (int) $this->conn->lastInsertId();
    }

    public function atualizar(RegistroCrefito $dados): bool
    {
        $sql = 'UPDATE dados_crefito SET
            registro = :registro,
            dt_registro = :dt_registro,
            servicos = :servicos,
            qtde_rt = :qtde_rt
        WHERE id = :id';

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute($this->mapearParametros($dados));
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM dados_crefito WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function listar(): array
    {
        $sql = 'SELECT * FROM dados_crefito ORDER BY id ASC';

        return $this->conn->query($sql)->fetchAll();
    }

    public function salvarOuAtualizar(RegistroCrefito $dados): void
    {
        $existe = $this->buscarPorId($dados->id);

        if ($existe) {
            $this->atualizar($dados);
        } else {
            $this->criar($dados);
        }
    }

    private function mapearParametros(RegistroCrefito $dados): array
    {
        return [
            'registro' => $dados->registro,
            'dt_registro' => $dados->dt_registro,
            'servicos' => $dados->servicos,
            'qtde_rt' => $dados->qtde_rt,
        ];
    }
}
