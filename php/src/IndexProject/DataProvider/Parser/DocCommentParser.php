<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Parser;

/**
 * Parser for PHPDoc comments.
 */
class DocCommentParser implements DocCommentParserInterface
{
    /**
     * Extract content from docblock before the first tag
     *
     * @param string $docComment The docblock comment to parse
     *
     * @return string|null Extracted docblock content
     */
    public function extractSpecificationBeforeFirstTag(string $docComment): ?string
    {
        if (empty($docComment)) {
            return null;
        }

        // Remove the opening /** and closing */ from the docblock
        $docComment = preg_replace('/^\/\*\*|\*\/$/s', '', $docComment);

        if ($docComment === null || $docComment === '') {
            return null;
        }

        // Remove asterisks at line beginnings
        $docComment = str_replace('*', '', $docComment);

        $lines = explode(PHP_EOL, $docComment);

        // Process each line: trim whitespace and filter empty lines
        $lines = array_map(fn($line) => trim($line), $lines);
        $lines = array_filter($lines, fn($line) => $line !== '');

        // Remove lines that start with tag indicators (@)
        $lines = array_filter($lines, fn($line) => !str_starts_with($line, '@'));

        // Remove lines that start with tag indicators ({@)
        $lines = array_filter($lines, fn($line) => !str_starts_with($line, '{@'));

        if (count($lines) === 0) {
            return null;
        }

        return implode(PHP_EOL, $lines);
    }
}
