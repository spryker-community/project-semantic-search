<?php

declare(strict_types=1);

namespace Spryker\AIAgent;

use NeuronAI\Providers\AIProviderInterface;

interface AIProviderResolverInterface
{
    public function getProvider(): AIProviderInterface;
}
