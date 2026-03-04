<?php

namespace Src\Entity;

class Relatorio
{
    public ?int $id = null;

    public string $nome;

    public int $qtdItemsProcessados;

    public int $qtdFalhas;

    public string $created_at;
}
