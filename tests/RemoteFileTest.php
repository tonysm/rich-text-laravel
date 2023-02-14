<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Attachables\RemoteFile;

class RemoteFileTest extends TestCase
{
    public function filesizesProvider()
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

    /**
     * @test
     *
     * @dataProvider filesizesProvider
     */
    public function converts_file_size_to_human_readable($bytes, $humanReadable)
    {
        $file = new RemoteFile([
            'url' => 'not-relevant',
            'content_type' => 'not-relevant',
            'filename' => 'not-relevant',
            'caption' => 'not-relevant',
            'filesize' => $bytes,
        ]);

        $this->assertEquals($humanReadable, $file->filesizeForHumans());
    }
}
