<?php

namespace App\Modules\connector;


class MsConnector extends DbConnector
{
    public function __construct(string $host, ?string $port, string $db, string $user, string $password)
    {
        parent::__construct(null, $host, $port, null, $db, $user, $password);
    }
}
