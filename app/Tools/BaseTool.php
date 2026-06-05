<?php

namespace App\Tools;

use App\Models\Client;
use App\Services\RemoteApiService;

abstract class BaseTool
{
    public function __construct(
        protected readonly RemoteApiService $remoteApi
    ) {}

    /**
     * The API endpoint for this tool (relative).
     */
    abstract public function endpoint(): string;

    /**
     * HTTP method to use.
     */
    public function method(): string
    {
        return 'GET';
    }

    /**
     * Execute this tool for the given client.
     */
    public function execute(Client $client): array
    {
        return $this->remoteApi->call(
            $client,
            $this->endpoint(),
            $this->method()
        );
    }
}
