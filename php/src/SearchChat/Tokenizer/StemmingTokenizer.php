<?php

declare(strict_types=1);

namespace Spryker\SearchChat\Tokenizer;

use Phpml\Tokenization\Tokenizer;
use Wamania\Snowball\Stemmer\English;

class StemmingTokenizer implements Tokenizer
{
    private $stemmer;

    public function __construct()
    {
        $this->stemmer = new English();
    }

    public function tokenize(string $text): array
    {
        // Split by whitespace
        $tokens = preg_split('/\s+/', $text);

        // Apply stemming to each token
        return array_map(function ($token) {
            // Clean the token from punctuation
            $token = preg_replace('/[^\p{L}\p{N}]/u', '', $token);

            // Apply stemming if token is not empty
            if (!empty($token)) {
                return $this->stemmer->stem($token);
            }

            return $token;
        }, $tokens);
    }
}
