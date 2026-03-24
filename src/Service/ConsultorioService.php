<?php

namespace Src\Service;

use Src\Config\Database;
use Src\Entity\Consultorio;

class ConsultorioService
{
    private \PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function criar(Consultorio $consultorio): int
    {
        $sql = 'INSERT INTO empresa (
            cpf, nome, nome_fantasia, area_atuacao,
            endereco, cidade, uf, email,
            registro, dt_registro
        ) VALUES (
            :cpf, :nome, :nome_fantasia, :area_atuacao,
            :endereco, :cidade, :uf, :email,
            :registro, :dt_registro
        )';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($this->mapearParametros($consultorio));

        return (int) $this->conn->lastInsertId();
    }

    public function atualizar(Consultorio $consultorio): bool
    {
        $sql = 'UPDATE consultorio SET
            cpf = :cpf,
            nome = :nome,
            nome_fantasia = :nome_fantasia,
            area_atuacao = :area_atuacao,
            endereco = :endereco,
            cidade = :cidade,
            uf = :uf,
            email = :email,
            registro = :registro,
            dt_registro = :dt_registro
        WHERE id = :id';

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute($this->mapearParametros($consultorio));
    }

    /*     public function buscarPorCnpj(string $cnpj): ?array
        {
            $sql = 'SELECT * FROM empresa WHERE cnpj = :cnpj';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['cnpj' => $cnpj]);

            $resultado = $stmt->fetch();

            return $resultado ?: null;
        } */

    public function buscarPorNome(string $nome): ?Consultorio
    {
        $sql = 'SELECT * FROM consultorio WHERE nome LIKE :nome';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'nome' => '%' . $nome . '%'
        ]);

        $resultado = $stmt->fetchObject(Consultorio::class);

        return $resultado ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM consultorio WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function listar(): array
    {
        $sql = 'SELECT * FROM consultorio ORDER BY nome ASC';

        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_CLASS, Consultorio::class);
    }

    /* public function atualizarIdDadosCrefito(string $cnpj, int $id): bool
    {
        $sql = 'UPDATE empresa
                SET id_dados_crefito = :id
                WHERE cnpj = :cnpj';

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'cnpj' => $cnpj,
        ]);
    } */

    private function mapearParametros(Consultorio $consultorio): array
    {
        return [
            'id' => $consultorio->id,
            'cpf' => $consultorio->cpf,
            'nome' => $consultorio->nome,
            'nome_fantasia' => $consultorio->nome_fantasia,
            'area_atuacao' => $consultorio->area_atuacao,
            'endereco' => $consultorio->endereco,
            'cidade' => $consultorio->cidade,
            'uf' => $consultorio->uf,
            'email' => $consultorio->email,
            'registro' => $consultorio->registro,
            'dt_registro' => $consultorio->dt_registro,
        ];
    }
}
