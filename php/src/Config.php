<?php

declare(strict_types=1);

namespace Spryker;

class Config
{
    public static function init(): self
    {
        static $factory = null;

        if ($factory === null) {
            $factory = new self();
        }

        return $factory;
    }

    public function getEmbeddingApi(): string
    {
        return $_ENV['EMBEDDING_API'];
    }

    public function getEmbeddingModel(): string
    {
        return $_ENV['EMBEDDING_MODEL'];
    }

    public function getChromaDbApi(): string
    {
        return $_ENV['CHROMA_DB_API'];
    }

    public function getOllamaApi(): string
    {
        return $_ENV['OLLAMA_API'];
    }

    public function getOllamaModel(): string
    {
        return $_ENV['OLLAMA_MODEL'];
    }

    public function getProjectAbsolutePath(): string
    {
        return $_ENV['PROJECT_ABSOLUTE_PATH'];
    }

    public function getProjectName(): string
    {
        return $_ENV['PROJECT_NAME'];
    }

    /**
     * @return array<string>
     */
    public function getProjectSrcDir(): array
    {
        $value = trim($_ENV['PROJECT_SRC_DIRS'] ?? '');
        $value = trim($value, ',');
        $value = trim($value, '/');
        $dirs = explode(',', $value);
        return array_map('trim', $dirs);
    }

    public function getMaxQueryResults(): int
    {
        return (int)$_ENV['MAX_QUERY_RESULTS'];
    }

    public function getMaxChunkDisplayResults(): int
    {
        return (int)$_ENV['MAX_CHUNK_DISPLAY_RESULTS'];
    }

    public function getAIProvider(): string
    {
        return $_ENV['AI_PROVIDER'] ?? 'ollama';
    }

    public function getDataTypeTrainData(): array
    {
        return [
            'PluginInterface' => [
                'plugin interface',
                'extension point',
                'dependency injection',
                'plugin contract',
                'plugin extension',
                'extension',
                'module extension',
            ],
            'Plugin' => [
                'plugin',
                'command handler',
                'condition handler',
                'plugin resource',
                'widget plugin',
                'event dispatcher',
                'plugin event',
                'command',
                'publisher',
            ],
            'ClientInterface' => [
                'client',
                'client api',
                'gateway call',
                'api call',
            ],
            'ServiceInterface' => [
                'service',
                'service layer',
                'service',
                'stateless',
                'service',
                'service interface',
            ],
            'Config' => [
                'config',
                'configuration',
                'configure',
                'environment',
                'config setting',
            ],
            'FacadeInterface' => [
                'facade',
                'api facade',
                'business logic facade',
                'controller facade',
                'facade method',
                'facade interface',
                'facade wrapper',
            ],
        ];
    }

    public function getQueryStopWords(): array
    {
        return [
            'Spryker',
            'project',
        ];
    }
}
