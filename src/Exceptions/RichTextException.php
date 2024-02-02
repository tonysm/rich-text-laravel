<?php

namespace Tonysm\RichTextLaravel\Exceptions;

use RuntimeException;

class RichTextException extends RuntimeException
{
    public static function missingRichTextFieldsProperty(string $class)
    {
        return new static(sprintf(
            'Missing protecetd property $richTextAttributes in the %s model.',
            $class,
        ));
    }

    public static function unknownRichTextFieldOnEagerLoading(string $field)
    {
        return new static(sprintf('Unknown rich text field: %s.', $field));
    }
}
