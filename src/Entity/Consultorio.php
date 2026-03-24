<?php

namespace Src\Entity;

class Consultorio
{
    public ?int $id = null;

    public $cpf;

    public $nome;

    public $nome_fantasia;

    public $area_atuacao;

    public $endereco;

    public $cidade;

    public $uf;

    public $email;

    public $registro;

    public $dt_registro;
}
