<?php
namespace App\Services\Obe;

abstract class BaseObeRepository
{
    public function __construct(protected ?ObeReadConnection $connection = null)
    {
        $this->connection ??= new ObeReadConnection();
    }
}
