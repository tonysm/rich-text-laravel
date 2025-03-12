<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Attachables\RemoteFile;

class RemoteFileTest extends TestCase
{
    public static function filesizesProvider(): array
    {
        return [
            'gigabytes' => [
                'bytes' => 1024 ** 3,
                'human' => '1.00 GB',
            ],
            'megabytes' => [
                'bytes' => 1024 ** 2,
                'human' => '1.00 MB',
            ],
            'kilobytes' => [
                'bytes' => 1024,
                'human' => '1.00 KB',
            ],
            'bytes' => [
                'bytes' => 1023,
                'human' => '1,023 Bytes',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('filesizesProvider')]
    public function converts_file_size_to_human_readable(int $bytes, string $human): void
    {
        $file = new RemoteFile([
            'url' => 'not-relevant',
            'content_type' => 'not-relevant',
            'filename' => 'not-relevant',
            'caption' => 'not-relevant',
            'filesize' => $bytes,
        ]);

        $this->assertEquals($human, $file->filesizeForHumans());
    }
}
