<?php

declare(strict_types=1);

namespace Spryker\AIAgent\SearchResultAssistant;

use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use Spryker\AIAgent\AIProviderResolver;

class SearchResultAssistantAgent extends Agent implements SearchResultAssistantAgentInterface
{
    protected function provider(): AIProviderInterface
    {
        return (new AIProviderResolver())
            ->getProvider();
    }

    public function instructions(): string
    {
        return 'You are an AI Assistant specialized in evaluating and summarising data by relevance to user question in Spryker php application.'
            . 'Read search results of in Spryker project.'
            . 'Read user\'s prompt.'
            . 'Do not edit or modify search results'
            . 'Do not generate code.'
            . 'Do not generate additional information for context.'
            . 'Answer as text with no special tags.'
            . 'Do not answer with json, markdown, table format.';
    }

}
