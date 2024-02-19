<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Support\Facades\DB;
use Workbench\App\Models\EncryptedMessage;
use Workbench\App\Models\Message;

class EncryptedModelTest extends TestCase
{
    /** @test */
    public function encrypt_content_based_on_encrypted_option_at_declaration_time()
    {
        $encryptedMessage = EncryptedMessage::create(['content' => 'Hello World']);
        $this->assertStringNotContainsString('Hello World', DB::table('rich_texts')->where('record_id', $encryptedMessage->id)->value('body'));
        $this->assertStringContainsString('Hello World', $encryptedMessage->refresh()->content->body->toHtml());

        $clearMessage = Message::create(['content' => 'Hello World']);
        $this->assertStringContainsString('Hello World', DB::table('rich_texts')->where('record_id', $clearMessage->id)->value('body'));
        $this->assertStringContainsString('Hello World', $clearMessage->refresh()->content->body->toHtml());
    }
}
