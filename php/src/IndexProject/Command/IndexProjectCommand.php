<?php

declare(strict_types=1);

namespace Spryker\IndexProject\Command;

use Spryker\ConfigResolverTrait;
use Spryker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class IndexProjectCommand extends Command
{
    use ConfigResolverTrait;

    protected const BATCH_SIZE = 100;

    protected function configure()
    {
        $this
            ->setName('project:index')
            ->setDescription('Index project into vector db');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $projectName = $this->getConfig()->getProjectName();

        $factory = Factory::init();

        $embeddingClient = $factory->createEmbeddingsClient();
        $vectorDbClient = $factory->createChromaDbClient();

        $output->writeln('<info>Project indexing has been started for ' . $projectName . '.</info>');
        $output->writeln('<info>Creating the collection for ' . $projectName . '.</info>');

        $vectorDbClient->createCollection($projectName);
        $collectionId = $vectorDbClient->getCollectionIdByName($projectName);

        $output->writeln('<info>Collection has been created for ' . $projectName . ' with ID:' . $collectionId . '.</info>');

        foreach ($factory->getDataProviders() as $dataProvider) {
            $output->writeln(PHP_EOL);
            $output->writeln('<info>Indexing ' . $dataProvider->getDataType() . ':</info>');
            $output->writeln('<info>Collecting data ...</info>');

            $parsedEntities = $dataProvider->getData();
            $count = count($parsedEntities);

            $output->writeln('<info>Collected '. $count . ' for '. $dataProvider->getDataType() . '</info>');

            $processedItems = 0;
            foreach (array_chunk($parsedEntities, static::BATCH_SIZE) as $batchedParsedEntities) {
                $output->write(sprintf("\rProgress: %s/%s of %s", $processedItems, $count, $dataProvider->getDataType()));

                [$prompts, $metadatas, $documents, $ids] = $this->prepareEmbeddingData($batchedParsedEntities, $dataProvider->getDataType());
                $embeddings = $embeddingClient->getMultiEmbeddingVectors($prompts);
                $vectorDbClient->addDocuments(
                    collectionId: $collectionId,
                    documents: $documents,
                    ids: $ids,
                    embeddings: $embeddings,
                    metadatas: $metadatas,
                );
                $processedItems += count($batchedParsedEntities);
            }
        }

        $output->writeln(PHP_EOL . '<info>Done! ' . round((microtime(true) - $startTime) / 60) . 'min taken</info>');

        return Command::SUCCESS;
    }

    protected function prepareEmbeddingData(array $batchedParsedEntities, string $type): array
    {
        $prompts = [];
        $metadatas = [];
        $documents = [];
        $ids = [];

        foreach ($batchedParsedEntities as $parsedEntity) {
            $prompts[] = $parsedEntity['code'];
            $metadatas[] = [
                'name' => $parsedEntity['name'],
                'type' => $type,
                'file_reference' => $parsedEntity['file_reference'],
                'code' => $parsedEntity['code'],
            ];
            $documents[] = $parsedEntity['code'];
            $ids[] = $parsedEntity['name'];
        }

        return [
            $prompts,
            $metadatas,
            $documents,
            $ids,
        ];
    }
}
