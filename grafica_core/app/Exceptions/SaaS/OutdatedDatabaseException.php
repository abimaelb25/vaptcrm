<?php

declare(strict_types=1);

namespace App\Exceptions\SaaS;

use RuntimeException;

class OutdatedDatabaseException extends RuntimeException
{
    /**
     * @param  list<string>  $missingTables
     */
    public function __construct(
        private readonly array $missingTables = [],
    ) {
        parent::__construct('Banco desatualizado. Execute php artisan migrate');
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return $this->missingTables;
    }
}