<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\TrixAttachment;

class TrixAttachmentTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function from_attributes(): void
    {
        $attributes = [
            'data-trix-attachment' => [
                'sgid' => '123',
                'contentType' => 'text/plain',
                'href' => 'http://example.com/',
                'filename' => 'example.txt',
                'filesize' => 12345,
                'previewable' => true,
            ],
            'data-trix-attributes' => [
                'caption' => 'hello',
            ],
        ];

        $attachment = $this->attachment([
            'sgid' => '123',
            'content_type' => 'text/plain',
            'href' => 'http://example.com/',
            'filename' => 'example.txt',
            'filesize' => '12345',
            'previewable' => 'true',
            'caption' => 'hello',
        ]);

        $this->assertAttributesJsonEqualsTo($attachment, $attributes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function filter_out_empty_attributes(): void
    {
        $attachment = $this->attachment([
            'sgid' => '123',
            'content_type' => 'text/plain',
            'href' => 'http://example.com/',
            'caption' => 'hello',
        ]);

        $this->assertArrayNotHasKey('filename', $attachment->attributes());
        $this->assertArrayNotHasKey('filesize', $attachment->attributes());
        $this->assertArrayNotHasKey('previewable', $attachment->attributes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function previewable_is_typecast(): void
    {
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => '']), ['previewable' => false]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => false]), ['previewable' => false]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => 'false']), ['previewable' => false]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => 'garbage']), ['previewable' => false]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => true]), ['previewable' => true]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => 'true']), ['previewable' => true]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function filesize_is_typecast_when_integerish(): void
    {
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => 123]), ['filesize' => 123]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => '123']), ['filesize' => 123]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => '3.5 MB']), ['filesize' => '3.5 MB']);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => null]), ['filesize' => null]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => '']), ['filesize' => '']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function attributes_strips_unmappable_attributes(): void
    {
        $attachment = $this->attachment([
            'sgid' => '123',
            'caption' => 'hello',
            'invalid' => 'garbage',
        ]);

        $this->assertAttachmentAttributesEqualsTo($attachment, ['sgid' => '123']);
        $this->assertTrixAttributesEqualsTo($attachment, ['caption' => 'hello']);
        $this->assertAttachmentAttributesDoesntHaveKey($attachment, 'invalid');
    }

    private function assertAttributesJsonEqualsTo(TrixAttachment $attachment, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->assertEquals($value, json_decode($attachment->node->getAttribute($key), true));
        }
    }

    private function assertAttachmentAttributesEqualsTo(TrixAttachment $attachment, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $actual = json_decode($attachment->node->getAttribute('data-trix-attachment'), true)[$key];

            if ($value === true) {
                $this->assertTrue($actual);
            } elseif ($value === false) {
                $this->assertFalse($actual);
            } else {
                $this->assertEquals(
                    $value,
                    $actual,
                    "Expected the `{$key}` attribute to be equal to {$value}, but it was `{$actual}`",
                );
            }
        }
    }

    private function assertTrixAttributesEqualsTo(TrixAttachment $attachment, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $actual = json_decode($attachment->node->getAttribute('data-trix-attributes'), true)[$key];

            if ($value === true) {
                $this->assertTrue($actual);
            } elseif ($value === false) {
                $this->assertFalse($actual);
            } else {
                $this->assertEquals(
                    $value,
                    $actual,
                    "Expected the `{$key}` attribute to be equal to {$value}, but it was `{$actual}`",
                );
            }
        }
    }

    private function assertAttachmentAttributesDoesntHaveKey(TrixAttachment $attachment, string $key): void
    {
        $actual = json_decode($attachment->node->getAttribute('data-trix-attachment') ?: '[]', true);

        $this->assertArrayNotHasKey($key, $actual);
    }

    private function attachment(array $attributes): TrixAttachment
    {
        return TrixAttachment::fromAttributes($attributes);
    }
}
