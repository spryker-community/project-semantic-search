<?php

declare(strict_types=1);

namespace Spryker\SearchChat\Command;

use NeuronAI\Chat\Messages\UserMessage;
use Spryker\ConfigResolverTrait;
use Spryker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PromptChatCommand extends Command
{
    use ConfigResolverTrait;

    /**
     * Commands that will exit the chat
     */
    private const EXIT_COMMANDS = [
        'exit',
        'quit',
        'q',
        'end',
    ];

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('project:search')
            ->setDescription('Ask a question and get an answer.')
            ->setHelp('This command allows you to ask a question and receive a response.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureOutputStyles($output);
        $io = new SymfonyStyle($input, $output);
        $factory = Factory::init();

        $promptHelper = $factory->createPromptHelper();
        $typeDetector = $factory->createTypeDetector();
        $embeddingClient = $factory->createEmbeddingsClient();
        $vectorDbClient = $factory->createChromaDbClient();
        $collectionId = $vectorDbClient->getCollectionIdByName($this->getConfig()->getProjectName());

        $output->writeln([
            '',
            '<header>╔════════════════════════════════════════════════════════════╗</>',
            '<header>║                                                            ║</>',
            '<header>║      Welcome to Spryker Project Semantic SearchChat!       ║</>',
            '<header>║           Type "exit" "quit" "q" "end" to exit             ║</>',
            '<header>║                                                            ║</>',
            '<header>╚════════════════════════════════════════════════════════════╝</>',
            '',
        ]);

        while (true) {
            $userQuestion = $io->ask('<question> >> I would like to find: </question>');
            if (empty($userQuestion) || in_array(strtolower(trim($userQuestion)), self::EXIT_COMMANDS, true)) {
                $output->writeln('<info> Goodbye! Have a great day!</info>');
                break;
            }

            $output->writeln("<processing>Processing query: \"$userQuestion\"...</processing>");
            $normalizedQuestion = $promptHelper->normalisePrompts($userQuestion);
            if (empty($normalizedQuestion)) {
                $output->writeln('<error>Could not process your question. Please try again.</error>');
                continue;
            }

            $types = $typeDetector->getTypesByPrompts($normalizedQuestion);
            $filters = [];
            if (!empty($types)) {
                $output->writeln("<processing>Detected relevant filters: " . implode(', ', $types) . "</processing>");
                $confirmTypes = $io->confirm('Confirm applied filters: ' . implode(', ', $types) . '.', true);
                if ($confirmTypes) {
                    $output->writeln("<processing>Applied filters: " . implode(', ', $types) . "</processing>");
                    $filters = $this->createFilters($types);
                }
            }

            $embeddedPrompt = $embeddingClient->getEmbeddingVector($normalizedQuestion);
            $answer = $vectorDbClient->queryByEmbedding(
                collectionId: $collectionId,
                queryEmbedding: $embeddedPrompt,
                numResults: $this->getConfig()->getMaxQueryResults(),
                filter: $filters,
            );

            $metadata = $answer['metadatas'][0] ?? [];
            if (empty($metadata)) {
                $output->writeln('<error>No results found. Please try a different query.</error>');
                continue;
            }

            $searchMode = $io->choice('Select how to display results', ['native', 'native-plus-ai'], 'native', false);
            if ($searchMode === 'native-plus-ai') {
                foreach (array_chunk($metadata, 10) as $result) {
                    $this->renderWithAI($input, $output, $factory, $normalizedQuestion, $result);
                    $more = $io->choice('More findings?', ['yes', 'no'], 'yes', false);
                    if ($more === 'no') {
                        break;
                    }
                }
            } elseif ($searchMode === 'native') {
                $this->renderResults($input, $output, $metadata);
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }

    private function renderWithAI(
        InputInterface  $input,
        OutputInterface $output,
        Factory         $factory,
        string          $normalizedQuestion,
        array           $metadata,
    ): void
    {
        $output->writeln(PHP_EOL);
        $output->writeln("┌────────────────────────────────────────────────────────────┐");
        $output->writeln("│ \e[1;36m Search Results \e[0m                                           │");
        $output->writeln("└────────────────────────────────────────────────────────────┘");

        $preparedDataString = PHP_EOL;
        foreach ($metadata as $index => $metadatum) {
            $output->writeln("\e[1;33m" . ($index + 1) . ". " . $metadatum['name'] . "\e[0m");
            $output->writeln("\e[0;90m" . $metadatum['file_reference'] . "\e[0m");
            $preparedDataString .= $metadatum['code'] . PHP_EOL;
        }

        $output->writeln(PHP_EOL);
        $output->writeln("┌────────────────────────────────────────────────────────────┐");
        $output->writeln("│ \e[1;36mAI Analysis Results...\e[0m                                     │");
        $output->writeln("└────────────────────────────────────────────────────────────┘");

        $prompt = 'Considering only the following PHP snippets without you own knowledge base:' . PHP_EOL . PHP_EOL . $preparedDataString . PHP_EOL
            . 'Answer my question: ' . PHP_EOL . 'I would like to find only relevant classes or methods related to "' . $normalizedQuestion . '".' . PHP_EOL . PHP_EOL .
            'Answer should have only information relevant to the question. If the provided data does not contain relevant information, then to try more. Your answer has to be as text list for console output.' . PHP_EOL;

        $response = $factory
            ->createSearchResultAssistantAgent()
            ->stream(
                new UserMessage($prompt)
            );

        $output->write("\e[1;36m");
        foreach ($response as $string) {
            $output->write($string);
        }
        $output->write("\e[0m");
        $output->write(PHP_EOL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $metadata
     *
     * @return void
     */
    private function renderResults(InputInterface $input, OutputInterface $output, array $metadata): void
    {
        $io = new SymfonyStyle($input, $output);

        $chunks = array_chunk($metadata, $this->getConfig()->getMaxChunkDisplayResults());

        foreach ($chunks as $index => $chunkedMetadata) {
            foreach ($chunkedMetadata as $resultIndex => $metadatum) {
                $overallIndex = ($index * $this->getConfig()->getMaxChunkDisplayResults()) + $resultIndex + 1;
                $output->writeln("┌────────────────────────────────────────────────────────────┐");
                $output->writeln("│ \e[1;36mResult #{$overallIndex}\e[0m                                                  │");
                $output->writeln("└────────────────────────────────────────────────────────────┘");
                $output->writeln("\e[1;33mFile:\e[0m " . $metadatum['file_reference']);
                $output->writeln("────────────────────────────────────────────────────────────");
                $output->writeln($metadatum['code']);
            }

            $output->writeln(PHP_EOL);

            if ($index < count($chunks) - 1) {
                $more = $io->choice('More findings?', ['yes', 'no'], 'yes', false);
                if ($more === 'no') {
                    break;
                }
            }
        }
    }

    /**
     * @param array $types
     *
     * @return array
     */
    private function createFilters(array $types): array
    {
        if (empty($types)) {
            return [];
        }

        return [
            'type' => [
                '$in' => $types,
            ],
        ];
    }

    /**
     * @param OutputInterface $output
     *
     * @return void
     */
    private function configureOutputStyles(OutputInterface $output): void
    {
        $formatter = $output->getFormatter();

        $formatter->setStyle('header', new OutputFormatterStyle('cyan', null, ['bold']));
        $formatter->setStyle('question', new OutputFormatterStyle('green', null, ['bold']));
        $formatter->setStyle('processing', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
    }
}
