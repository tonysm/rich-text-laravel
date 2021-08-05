<?php

namespace Tonysm\RichTextLaravel;

class TrixContent
{
    public const ATTACHMENT_SELECTOR = '//*[@data-trix-attachment]';
    public const ATTACHABLE_SELECTOR = '//rich-text-attachable';
    public const GALLERY_SELECTOR = '//*[contains(concat(" ",normalize-space(@class)," ")," attachment-gallery ")]';
}
