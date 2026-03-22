<?php

namespace Tonysm\RichTextLaravel\Exceptions;

use RuntimeException;

class RichTextException extends RuntimeException
{
    public static function missingRichTextFieldsProperty(string $class): static
    {
        return new static(sprintf(
            'The %s model must declare rich text fields using either #[RichTextAttributes] class attributes or the protected $richTextAttributes property.',
            $class,
        ));
    }

    public static function ambiguousRichTextConfiguration(string $class): static
    {
        return new static(sprintf(
            'The %s model uses both #[RichTextAttributes] attributes and the $richTextAttributes property. Please use one or the other, not both.',
            $class,
        ));
    }

    public static function unknownRichTextFieldOnEagerLoading(string $field): static
    {
        return new static(sprintf('Unknown rich text field: %s.', $field));
    }
}
