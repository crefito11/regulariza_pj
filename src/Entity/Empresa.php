<?php

namespace Src\Entity;

class Empresa
{
    public ?int $id = null;
    public $cnpj;
    public $razao_social;
    public $nome_fantasia;
    public $natureza_juridica;
    public $slu_ei;
    public $atividade;
    public $cnae_principal;
    public $inscricao_estadual;
    public $endereco;
    public $cidade;
    public $email;
    public $telefone;
    public $uf;
    public $situacao_cadastral;
    public $descricao_matriz_filial;
}
