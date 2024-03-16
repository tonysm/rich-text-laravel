<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\Support\Facades\Crypt;

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
     * Override the way the package handles encryption.
     */
    public static function encryptUsing($encryption, $decryption): void
    {
        static::$encryptHandler = $encryption;
        static::$decryptHandler = $decryption;
    }

    /**
     * Configures the Rich Text Laravel package to store encrypted data as string,
     * instead of using Laravel's default encryption mode, which serializes the
     * content before encrypting it.
     */
    public static function encryptAsString(): void
    {
        static::$encryptHandler = fn ($value) => Crypt::encryptString($value);
        static::$decryptHandler = fn ($value) => ($value ? Crypt::decryptString($value) : $value);
    }

    public static function clearEncryptionHandlers(): void
    {
        static::encryptUsing(null, null);
    }

    public static function encrypt($value, $model, $key): string
    {
        $encrypt = static::$encryptHandler ??= fn ($value) => Crypt::encrypt($value);

        return call_user_func($encrypt, $value, $model, $key);
    }

    public static function decrypt($value, $model, $key): ?string
    {
        $decrypt = static::$decryptHandler ??= fn ($value) => Crypt::decrypt($value);

        return $value ? call_user_func($decrypt, $value, $model, $key) : $value;
    }
}
