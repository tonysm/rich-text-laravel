<?php

namespace Tonysm\RichTextLaravel\Exceptions;

use RuntimeException;

class RichTextException extends RuntimeException
{
    public static function missingRichTextFieldsProperty(string $class): static
    {
        return new static(sprintf(
            'Missing protecetd property $richTextAttributes in the %s model.',
            $class,
        ));
    }

    public static function unknownRichTextFieldOnEagerLoading(string $field): static
    {
        return new static(sprintf('Unknown rich text field: %s.', $field));
    }
}
