<?php

namespace Src\Entity;

class Log
{
    public ?int $id = null;

    public int $id_relatorio;

    public string $mensagem;

    public string $created_at;
}
