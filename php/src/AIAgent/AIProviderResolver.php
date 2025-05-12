<?php

declare(strict_types=1);

namespace Spryker\AIAgent;

use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Ollama\Ollama;
use Spryker\ConfigResolverTrait;

class AIProviderResolver implements AIProviderResolverInterface
{
    use ConfigResolverTrait;

    public function getProvider(): AIProviderInterface
    {
        return match ($this->getConfig()->getAIProvider()) {
            'ollama' => new Ollama(
                url: $this->getConfig()->getOllamaApi() . '/api',
                model: $this->getConfig()->getOllamaModel(),
            ),
        };
    }
}
