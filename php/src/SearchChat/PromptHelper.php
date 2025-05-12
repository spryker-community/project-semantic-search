<?php

declare(strict_types=1);

namespace Spryker\SearchChat;

use Phpml\FeatureExtraction\StopWords;
use Phpml\Tokenization\Tokenizer;

class PromptHelper
{
    public function __construct(
        private StopWords $stopWords,
        private Tokenizer $tokenizer,
    )
    {
    }

    public function normalisePrompts(string $prompt): string
    {
        $normalizedQuestion = strtolower(trim($prompt));

        $tokens = array_filter(
            $this->tokenizer->tokenize($normalizedQuestion),
            fn(string $token) => $this->stopWords->isStopWord($token) === false,
        );

        return implode(' ', $tokens);
    }
}
