<?php

namespace App\Exceptions;

use Exception;

class EAuth extends Exception
{
    public function __construct()
    {
        parent::__construct(__('Usuário não está autenticado'), 100);
    }
}
