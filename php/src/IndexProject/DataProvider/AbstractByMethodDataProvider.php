<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider;

use Spryker\Config;
use Spryker\IndexProject\DataProvider\Finder\ProjectPathProviderInterface;
use Spryker\IndexProject\DataProvider\Parser\ClassMethodParser;

abstract class AbstractByMethodDataProvider implements DataProviderInterface
{
    public function __construct(
        protected ProjectPathProviderInterface $projectPathProvider,
        protected ClassMethodParser $classMethodExtractor,
        protected Config            $config,
    ){
    }

    public function getData(): array
    {
        $data = [];
        foreach ($this->projectPathProvider->getProjectPaths() as $path) {
            $fileContent = file_get_contents($path);
            $parsedData = $this->classMethodExtractor->parse($fileContent);
            if ($parsedData === null || count($parsedData) === 0) {
                continue;
            }

            $parsedData = array_map(function (array $parsedDatum) use ($path) {
                $parsedDatum['file_reference'] = str_replace(PROJECT_DIR, $this->config->getProjectAbsolutePath(), $path) . ':' . $parsedDatum['line'];
                return $parsedDatum;
            }, $parsedData);

            $data = array_merge($data, $parsedData);
        }

        return $data;
    }
}
