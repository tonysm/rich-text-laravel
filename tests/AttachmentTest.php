<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\GlobalId\Facades\Locator;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;
use Tonysm\RichTextLaravel\Attachment;
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

        $this->assertTrue($attachable->is(Locator::locateSigned($trixAttachment->attributes()['sgid'], [
            'for' => 'rich-text-laravel',
        ])));
        $this->assertEquals($attachable->richTextContentType(), $trixAttachment->attributes()['contentType']);
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

        $this->assertTrue($attachable->is(Locator::locateSigned($trixAttachment->attributes()['sgid'], ['for' => 'rich-text-laravel'])));
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

    /** @test */
    public function equality()
    {
        $userAttachment = Attachment::fromAttachable($user = $this->attachable());
        $sameUserAttachment = Attachment::fromAttachable($user);
        $anotherUserAttachment = Attachment::fromAttachable($this->attachable());
        $imageAttachment = Attachment::fromAttachable($image = $this->imageAttachable('blue.png'));
        $sameImageAttachment = Attachment::fromAttachable($image);
        $anotherImageAttachment = Attachment::fromAttachable($this->imageAttachable('red.png'));

        $this->assertTrue($userAttachment->is($sameUserAttachment));
        $this->assertFalse($userAttachment->is($anotherUserAttachment));

        $this->assertTrue($imageAttachment->is($sameImageAttachment));
        $this->assertFalse($imageAttachment->is($anotherImageAttachment));

        $this->assertFalse($userAttachment->is($imageAttachment));
    }

    private function attachable(): User
    {
        return User::create([
            'name' => 'Some user',
        ]);
    }

    public function imageAttachable(string $filename): RemoteImage
    {
        return new RemoteImage([
            'url' => 'http://example.com/' . $filename,
            'width' => 200,
            'height' => 200,
            'content_type' => 'image/png',
            'caption' => 'hey there',
            'filename' => $filename,
            'filesize' => 200,
        ]);
    }
}

class UseWithCustomPlainTextRender extends User
{
    protected $table = 'users';

    public function richTextAsPlainText(): string
    {
        return 'custom plain text render';
    }
}
