<?php

namespace Src\Service;

use Src\Config\Database;
use Src\Entity\DadosTemp;

class DadosTempService
{
    private \PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function criar(DadosTemp $dadosTemp): int
    {
        $sql = 'INSERT INTO dados_empresa_temp (
            cnpj, email, inscricao_estadual, registro, dt_registro,
            servicos, qtde_rt
        ) VALUES (
            :cnpj, :email, :inscricao_estadual, :registro, :dt_registro,
            :servicos, :qtde_rt
        )';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($this->mapearParametros($dadosTemp));

        return (int) $this->conn->lastInsertId();
    }

    public function atualizar(DadosTemp $dadosTemp): bool
    {
        $sql = 'UPDATE dados_empresa_temp SET
            email, :email,
            inscricao_estadual = :inscricao_estadual,
            registro = :registro,
            dt_registro = :dt_registro,
            servicos = :servicos,
            qtde_rt = :qtde_rt
        WHERE cnpj = :cnpj';

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute($this->mapearParametros($dadosTemp));
    }

    public function buscarPorCnpj(string $cnpj): ?array
    {
        $sql = 'SELECT * FROM dados_empresa_temp WHERE cnpj = :cnpj';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['cnpj' => $cnpj]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM dados_empresa_temp WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function listar(): array
    {
        $sql = 'SELECT * FROM dados_empresa_temp ORDER BY id ASC';

        return $this->conn->query($sql)->fetchAll();
    }

    public function salvarOuAtualizar(DadosTemp $dadosTemp): void
    {
        $existe = $this->buscarPorCnpj($dadosTemp->cnpj);

        if ($existe) {
            $this->atualizar($dadosTemp);
        } else {
            $this->criar($dadosTemp);
        }
    }

    private function mapearParametros(DadosTemp $dadosTemp): array
    {
        return [
            'cnpj' => $dadosTemp->cnpj,
            'email' => $dadosTemp->email,
            'inscricao_estadual' => $dadosTemp->inscricao_estadual,
            'registro' => $dadosTemp->registro,
            'dt_registro' => $dadosTemp->dt_registro,
            'servicos' => $dadosTemp->servicos,
            'qtde_rt' => $dadosTemp->qtde_rt,
        ];
    }
}
