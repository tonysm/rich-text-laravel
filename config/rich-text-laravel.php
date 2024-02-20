<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Rich Text Model
     |--------------------------------------------------------------------------
     |
     | When using the suggested database structure, all your Rich Text content will be
     | stored in the same Database table. All interactions with that table happens
     | using this Eloquent Model. You can override this if you really need to.
     |
     */
    'model' => \Tonysm\RichTextLaravel\Models\RichText::class,

    /*
     |--------------------------------------------------------------------------
     | Encrypted Rich Text Model
     |--------------------------------------------------------------------------
     |
     | When setting the `encrypted` option to `true` on the attribute, the package
     | will use this model instead of the base RichText model.
     |
     */
    'encrypted_model' => \Tonysm\RichTextLaravel\Models\EncryptedRichText::class,

    /*
     |--------------------------------------------------------------------------
     | Supported Files Content-Types
     |--------------------------------------------------------------------------
     |
     | When attaching non-image files to Trix, you can control here which files
     | you want to handle and render in the default "remote file" template
     | by explicitly adding your supported Content-Types to this list.
     |
     */
    'supported_files_content_types' => [
        'application/pdf',
        'text/csv',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ],
];
