<?php

declare(strict_types=1);

namespace Spryker\SearchChat\TypeDetector;

use Phpml\Classification\NaiveBayes;
use Phpml\FeatureExtraction\StopWords\English;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Spryker\Config;
use Spryker\SearchChat\Tokenizer\StemmingTokenizer;

class TypeDetector implements TypeDetectorInterface
{
    private WordTokenizer $wordTokenizer;

    private StemmingTokenizer $stemmingTokenizer;

    public function __construct(
        private Config $config,
    )
    {
        $this->stemmingTokenizer = new StemmingTokenizer();
        $this->wordTokenizer = new WordTokenizer();
    }

    public function getTypesByPrompts(string $prompt): array
    {
        $matches = $this->getTokenMatches($prompt);

        if (empty($matches)) {
            return [];
        }

        $predictedLabels = $this->getPredictedLabels($prompt);
        $types = array_merge(array_keys($matches), $predictedLabels);

        return array_unique($types);
    }

    private function getTokenMatches(string $prompt): array
    {
        $words = $this->stemmingTokenizer->tokenize($prompt);

        $tokens = $words;
        for ($i = 0; $i < count($words) - 1; $i++) {
            $tokens[] = $words[$i] . ' ' . $words[$i + 1];
        }

        $matches = [];
        foreach ($this->config->getDataTypeTrainData() as $category => $phrases) {
            foreach ($phrases as $phrase) {
                $phrase = trim(strtolower($phrase));
                $words = $this->wordTokenizer->tokenize($phrase);
                $phrase = implode(' ', $words);
                $words = $this->stemmingTokenizer->tokenize($phrase);
                $phrase = implode(' ', $words);
                if (in_array($phrase, $tokens, true)) {
                    $matches[$category][] = $phrase;
                }
            }
        }

        return $matches;
    }

    private function getPredictedLabels(string $prompt): array
    {
        [$samples, $labels] = $this->getTrainData();

        $vectorizer = new TokenCountVectorizer(
            $this->stemmingTokenizer,
            new English(),
        );

        $vectorizer->fit($samples);
        $vectorizer->transform($samples);

        $tfIdfTransformer = new TfIdfTransformer();
        $tfIdfTransformer->fit($samples);
        $tfIdfTransformer->transform($samples);

        $classifier = new NaiveBayes();
        $classifier->train($samples, $labels);

        $next = [$prompt];
        $vectorizer->transform($next);
        $tfIdfTransformer->transform($next);

        return $classifier->predict($next);
    }

    private function getTrainData(): array
    {
        $samples = [];
        $labels = [];

        foreach ($this->config->getDataTypeTrainData() as $category => $terms) {
            foreach ($terms as $term) {
                $samples[] = $term;
                $labels[] = $category;
            }
        }

        return [
            $samples,
            $labels,
        ];
    }
}
