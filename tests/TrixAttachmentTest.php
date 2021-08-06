<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\TrixAttachment;

class TrixAttachmentTest extends TestCase
{
    /** @test */
    public function from_attributes()
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

    /** @test */
    public function previewable_is_typecast()
    {
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => '']), ["previewable" => false]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => false]), ["previewable" => false]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => "false"]), ["previewable" => false]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => "garbage"]), ["previewable" => false]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => true]), ["previewable" => true]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['previewable' => 'true']), ["previewable" => true]);
    }

    /** @test */
    public function filesize_is_typecast_when_integerish()
    {
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => 123]), ['filesize' => 123]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => '123']), ['filesize' => 123]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => '3.5 MB']), ['filesize' => '3.5 MB']);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => null]), ['filesize' => null]);
        $this->assertAttachmentAttributesEqualsTo($this->attachment(['filesize' => '']), ['filesize' => '']);
    }

    /** @test */
    public function attributes_strips_unmappable_attributes()
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
