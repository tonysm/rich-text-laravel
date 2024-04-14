<?php

namespace Tonysm\RichTextLaravel;

use Closure;
use DOMElement;
use Illuminate\Support\Facades\Crypt;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;

class RichTextLaravel
{
    /**
     * The handler responsible for encrypting the rich text attributes.
     *
     * @var callable
     */
    protected static $encryptHandler;

    /**
     * The handle responsible for decrypting the rich text attributes.
     *
     * @var callable
     */
    protected static $decryptHandler;

    /**
     * The custom content attachment resolver (if any).
     *
     * @var callable
     */
    protected static $customAttachablesResolver;

    /**
     * Override the way the package handles encryption.
     */
    public static function encryptUsing($encryption, $decryption): void
    {
        static::$encryptHandler = $encryption;
        static::$decryptHandler = $decryption;
    }

    /**
     * This will be the default.
     */
    public static function encryptAsString(): void
    {
        static::encryptUsing(null, null);
    }

    public static function clearEncryptionHandlers(): void
    {
        static::encryptUsing(null, null);
    }

    public static function encrypt($value, $model, $key): string
    {
        $encrypt = static::$encryptHandler ??= fn ($value) => Crypt::encryptString($value);

        return call_user_func($encrypt, $value, $model, $key);
    }

    public static function decrypt($value, $model, $key): ?string
    {
        $decrypt = static::$decryptHandler ??= fn ($value) => Crypt::decryptString($value);

        return $value ? call_user_func($decrypt, $value, $model, $key) : $value;
    }

    public static function withCustomAttachables(Closure|callable|null $customAttachablesResolver): void
    {
        static::$customAttachablesResolver = $customAttachablesResolver;
    }

    public static function clearCustomAttachables(): void
    {
        static::withCustomAttachables(null);
    }

    public static function attachableFromCustomResolver(DOMElement $node): ?AttachableContract
    {
        $resolver = static::$customAttachablesResolver ?? fn () => null;

        return $resolver($node);
    }
}
