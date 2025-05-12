<?php

declare(strict_types=1);

namespace Spryker\VectorDBClient\ChromaDBClient;

/**
 * Configuration class for ChromaDB client.
 *
 * This class encapsulates all configuration parameters needed
 * to connect to and interact with a ChromaDB instance.
 */
class ChromaDbConfig
{
    /**
     * @param string $host The host URL of the ChromaDB service
     * @param string $apiVersion The API version to use
     * @param string $tenant The tenant name within ChromaDB
     * @param string $database The database name within the tenant
     */
    public function __construct(
        protected string $host = 'http://chromadb:8000',
        protected string $apiVersion = 'v2',
        protected string $tenant = 'default_tenant',
        protected string $database = 'default_database',
    ) {
    }

    /**
     * Get the base URL for the ChromaDB API.
     *
     * @return string The fully constructed base URL
     */
    public function getBaseUrlWithTenantAndDatabase(): string
    {
        return sprintf(
            '%s/api/%s/tenants/%s/databases/%s',
            $this->host,
            $this->apiVersion,
            $this->tenant,
            $this->database,
        );
    }

    /**
     * Get the host URL.
     *
     * @return string The host URL
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get the API version.
     *
     * @return string The API version
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Get the tenant name.
     *
     * @return string The tenant name
     */
    public function getTenant(): string
    {
        return $this->tenant;
    }

    /**
     * Get the database name.
     *
     * @return string The database name
     */
    public function getDatabase(): string
    {
        return $this->database;
    }
}
