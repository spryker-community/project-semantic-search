<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Parser;

/**
 * Interface for parsing PHPDoc comments.
 */
interface DocCommentParserInterface
{
    /**
     * Extract content from docblock before the first tag
     *
     * @param string $docComment The docblock comment to parse
     *
     * @return string|null Extracted docblock content
     */
    public function extractSpecificationBeforeFirstTag(string $docComment): ?string;
}
