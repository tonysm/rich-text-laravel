<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\GlobalId;
use Tonysm\RichTextLaravel\Tests\Stubs\User;

class AttachmentTest extends TestCase
{
    /** @test */
    public function from_attachable()
    {
        $attachment = Attachment::fromAttachable($attachable = $this->attachable(), ["caption" => "Hey, there"]);

        $this->assertSame($attachment->attachable, $attachable);
        $this->assertEquals('Hey, there', $attachment->caption());
    }

    /** @test */
    public function proxies_missing_methods_to_attachable()
    {
        $attachment = Attachment::fromAttachable($attachable = $this->attachable());

        $this->assertEquals($attachable->getKey(), $attachment->getKey());
    }

    /** @test */
    public function converts_to_trix_attachment()
    {
        $attachable = $this->attachable();

        $attachment = Attachment::fromNode(Attachment::nodeFromAttributes([
            'sgid' => $attachable->richTextSgid(),
            'caption' => 'Hey, there',
        ]));

        $trixAttachment = $attachment->toTrixAttachment();

        $this->assertTrue($attachable->is(GlobalId::fromStorage($trixAttachment->attributes()['sgid'])->record));
        $this->assertEquals($attachable->richTextContentType(), $trixAttachment->attributes()['contentType']);
        $this->assertEquals($attachable->richTextFilename(), $trixAttachment->attributes()['filename']);
        $this->assertEquals($attachable->richTextFilesize(), $trixAttachment->attributes()['filesize']);
        $this->assertEquals('Hey, there', $trixAttachment->attributes()['caption']);

        $this->assertNotEmpty($attachable->richTextRender());
        $this->assertEquals($attachable->richTextRender(), $trixAttachment->attributes()['content']);
    }

    /** @test */
    public function converts_to_trix_attachment_with_content()
    {
        $attachable = $this->attachable();

        $attachment = Attachment::fromNode(Attachment::nodeFromAttributes([
            'sgid' => $attachable->richTextSgid(),
            'caption' => 'Hey, there',
        ]));

        $trixAttachment = $attachment->toTrixAttachment('trix content');

        $this->assertTrue($attachable->is(GlobalId::fromStorage($trixAttachment->attributes()['sgid'])->record));
        $this->assertEquals($attachable->richTextContentType(), $trixAttachment->attributes()['contentType']);
        $this->assertEquals('Hey, there', $trixAttachment->attributes()['caption']);

        $this->assertEquals('trix content', $trixAttachment->attributes()['content']);
    }

    /** @test */
    public function converst_to_plain_text()
    {
        $attachment = Attachment::fromAttachable($this->attachable(), ['caption' => 'hey, there']);
        $this->assertEquals('hey, there', $attachment->toPlainText());

        $attachment = Attachment::fromAttachable(UseWithCustomPlainTextRender::create(['name' => 'hey']));
        $this->assertEquals('custom plain text render', $attachment->toPlainText());
    }

    private function attachable(): User
    {
        return User::create([
            'name' => 'Some user',
        ]);
    }
}

class UseWithCustomPlainTextRender extends User
{
    protected $table = 'users';

    public function plainTextRender(): string
    {
        return 'custom plain text render';
    }
}
