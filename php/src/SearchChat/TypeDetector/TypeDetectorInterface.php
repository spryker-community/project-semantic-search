<?php

declare(strict_types=1);

namespace Spryker\SearchChat\TypeDetector;

interface TypeDetectorInterface
{
    public function getTypesByPrompts(string $prompt): array;
}
