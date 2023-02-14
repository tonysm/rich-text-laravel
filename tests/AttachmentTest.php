<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\GlobalId\Facades\Locator;
use Tonysm\RichTextLaravel\Attachables\RemoteFile;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Tests\Stubs\User;

class AttachmentTest extends TestCase
{
    /** @test */
    public function from_attachable()
    {
        $attachment = Attachment::fromAttachable($attachable = $this->attachable(), ['caption' => 'Hey, there']);

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
    public function converst_to_html()
    {
        $attachment = Attachment::fromAttachable($this->attachable(), ['caption' => 'hey, there']);
        $this->assertEquals('<rich-text-attachment caption="hey, there" sgid="eyJzZ2lkIjoiZ2lkOlwvXC9yaWNoLXRleHQtbGFyYXZlbFwvVG9ueXNtJTVDUmljaFRleHRMYXJhdmVsJTVDVGVzdHMlNUNTdHVicyU1Q1VzZXJcLzEiLCJwdXJwb3NlIjoicmljaC10ZXh0LWxhcmF2ZWwiLCJleHBpcmVzX2F0IjpudWxsfQ==--3442b51e2f1b56d5928b4654847c19e4d4b03f57dcfa02bcd6cbdc01ccec004c" content-type="application/octet-stream"></rich-text-attachment>', $attachment->toHtml());
        $this->assertEquals('<rich-text-attachment caption="hey, there" sgid="eyJzZ2lkIjoiZ2lkOlwvXC9yaWNoLXRleHQtbGFyYXZlbFwvVG9ueXNtJTVDUmljaFRleHRMYXJhdmVsJTVDVGVzdHMlNUNTdHVicyU1Q1VzZXJcLzEiLCJwdXJwb3NlIjoicmljaC10ZXh0LWxhcmF2ZWwiLCJleHBpcmVzX2F0IjpudWxsfQ==--3442b51e2f1b56d5928b4654847c19e4d4b03f57dcfa02bcd6cbdc01ccec004c" content-type="application/octet-stream"></rich-text-attachment>', (string) $attachment);

        $attachment = Attachment::fromAttachable(User::create(['name' => 'hey']));
        $this->assertEquals('<rich-text-attachment sgid="eyJzZ2lkIjoiZ2lkOlwvXC9yaWNoLXRleHQtbGFyYXZlbFwvVG9ueXNtJTVDUmljaFRleHRMYXJhdmVsJTVDVGVzdHMlNUNTdHVicyU1Q1VzZXJcLzIiLCJwdXJwb3NlIjoicmljaC10ZXh0LWxhcmF2ZWwiLCJleHBpcmVzX2F0IjpudWxsfQ==--e260941c7b4f1875c536edf100274fbb3c4c8e3a1b41afd00efa16cf258a1982" content-type="application/octet-stream"></rich-text-attachment>', $attachment->toHtml());
        $this->assertEquals('<rich-text-attachment sgid="eyJzZ2lkIjoiZ2lkOlwvXC9yaWNoLXRleHQtbGFyYXZlbFwvVG9ueXNtJTVDUmljaFRleHRMYXJhdmVsJTVDVGVzdHMlNUNTdHVicyU1Q1VzZXJcLzIiLCJwdXJwb3NlIjoicmljaC10ZXh0LWxhcmF2ZWwiLCJleHBpcmVzX2F0IjpudWxsfQ==--e260941c7b4f1875c536edf100274fbb3c4c8e3a1b41afd00efa16cf258a1982" content-type="application/octet-stream"></rich-text-attachment>', (string) $attachment);
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
        $fileAttachment = Attachment::fromAttachable($file = $this->fileAttachable('file.csv'));
        $sameFileAttachment = Attachment::fromAttachable($file);
        $anotherFileAttachment = Attachment::fromAttachable($this->fileAttachable('another-file.csv'));

        $this->assertTrue($userAttachment->is($sameUserAttachment));
        $this->assertFalse($userAttachment->is($anotherUserAttachment));

        $this->assertTrue($imageAttachment->is($sameImageAttachment));
        $this->assertFalse($imageAttachment->is($anotherImageAttachment));

        $this->assertTrue($fileAttachment->is($sameFileAttachment));
        $this->assertFalse($fileAttachment->is($anotherFileAttachment));

        $this->assertFalse($userAttachment->is($imageAttachment));
        $this->assertFalse($userAttachment->is($fileAttachment));
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
            'url' => 'http://example.com/'.$filename,
            'width' => 200,
            'height' => 200,
            'content_type' => 'image/png',
            'caption' => 'hey there',
            'filename' => $filename,
            'filesize' => 200,
        ]);
    }

    public function fileAttachable(string $filename): RemoteFile
    {
        return new RemoteFile([
            'url' => 'http://example.com/'.$filename,
            'content_type' => 'text/csv',
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
