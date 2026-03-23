<?php

namespace Src\Service;

use Src\Config\Database;
use Src\Entity\Empresa;

class EmpresaService
{
    private \PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function criar(Empresa $empresa): int
    {
        $sql = 'INSERT INTO empresa (
            cnpj, razao_social, nome_fantasia, natureza_juridica,
            slu_ei, atividade, cnae_principal, inscricao_estadual,
            endereco, cidade, email, telefone, uf,
            situacao_cadastral, descricao_matriz_filial, id_dados_crefito
        ) VALUES (
            :cnpj, :razao_social, :nome_fantasia, :natureza_juridica,
            :slu_ei, :atividade, :cnae_principal, :inscricao_estadual,
            :endereco, :cidade, :email, :telefone, :uf,
            :situacao_cadastral, :descricao_matriz_filial, :id_dados_crefito
        )';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($this->mapearParametros($empresa));

        return (int) $this->conn->lastInsertId();
    }

    public function atualizar(Empresa $empresa): bool
    {
        $sql = 'UPDATE empresa SET
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
            descricao_matriz_filial = :descricao_matriz_filial,
            id_dados_crefito = :id_dados_crefito
        WHERE cnpj = :cnpj';

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute($this->mapearParametros($empresa));
    }

    public function buscarPorCnpj(string $cnpj): ?array
    {
        $sql = 'SELECT * FROM empresa WHERE cnpj = :cnpj';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['cnpj' => $cnpj]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function buscarPorCnpjOb(string $cnpj): ?Empresa
    {
        $sql = 'SELECT * FROM empresa WHERE cnpj = :cnpj';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['cnpj' => $cnpj]);

        $resultado = $stmt->fetchObject(Empresa::class);

        return $resultado ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM empresa WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function listaBaixadas(): ?array
    {
        $sql = 'SELECT * FROM vw_empresas_baixadas';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->fetchAll();

        return $resultado ?: null;
    }

    public function listar(): array
    {
        $sql = 'SELECT * FROM empresa ORDER BY razao_social ASC';

        return $this->conn->query($sql)->fetchAll();
    }

    public function salvarOuAtualizar(Empresa $empresa): void
    {
        $existe = $this->buscarPorCnpj($empresa->cnpj);

        if ($existe) {
            $this->atualizar($empresa);
        } else {
            $this->criar($empresa);
        }
    }

    public function atualizarIdDadosCrefito(string $cnpj, int $id): bool
    {
        $sql = 'UPDATE empresa 
                SET id_dados_crefito = :id 
                WHERE cnpj = :cnpj';

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'cnpj' => $cnpj,
        ]);
    }

    private function mapearParametros(Empresa $empresa): array
    {
        return [
            'cnpj' => $empresa->cnpj,
            'razao_social' => $empresa->razao_social,
            'nome_fantasia' => $empresa->nome_fantasia,
            'natureza_juridica' => $empresa->natureza_juridica,
            'slu_ei' => $empresa->slu_ei ? 1 : 0,
            'atividade' => $empresa->atividade,
            'cnae_principal' => $empresa->cnae_principal,
            'inscricao_estadual' => $empresa->inscricao_estadual,
            'endereco' => $empresa->endereco,
            'cidade' => $empresa->cidade,
            'email' => $empresa->email,
            'telefone' => $empresa->telefone,
            'uf' => $empresa->uf,
            'situacao_cadastral' => $empresa->situacao_cadastral,
            'descricao_matriz_filial' => $empresa->descricao_matriz_filial,
            'id_dados_crefito' => $empresa->id_dados_crefito ?? null,
        ];
    }
}
