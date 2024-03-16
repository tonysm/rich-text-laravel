<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Support\Facades\DB;
use Tonysm\RichTextLaravel\RichTextLaravel;
use Workbench\App\Models\EncryptedMessage;
use Workbench\App\Models\Message;

class EncryptedModelTest extends TestCase
{
    /** @test */
    public function encrypt_content_based_on_encrypted_option_at_declaration_time()
    {
        $encryptedMessage = EncryptedMessage::create(['content' => 'Hello World']);
        $this->assertEncryptedRichTextAttribute($encryptedMessage, 'content', 'Hello World');

        $clearMessage = Message::create(['content' => 'Hello World']);
        $this->assertNotEncryptedRichTextAttribute($clearMessage, 'content', 'Hello World');
    }

    /** @test */
    public function encrypts_as_string()
    {
        RichTextLaravel::encryptAsString();

        $encryptedMessage = EncryptedMessage::create(['content' => 'Hello World']);
        $this->assertEncryptedRichTextAttribute($encryptedMessage, 'content', 'Hello World');
    }

    private function assertEncryptedRichTextAttribute($model, $field, $expectedValue)
    {
        $this->assertStringNotContainsString($expectedValue, $encrypted = DB::table('rich_texts')->where('record_id', $model->id)->value('body'));
        $this->assertEquals($expectedValue, RichTextLaravel::decrypt($encrypted, $model, $field));
        $this->assertStringContainsString($expectedValue, $model->refresh()->{$field}->body->toHtml());
    }

    public function assertNotEncryptedRichTextAttribute($model, $field, $expectedValue)
    {
        $this->assertStringContainsString($expectedValue, DB::table('rich_texts')->where('record_id', $model->id)->value('body'));
        $this->assertStringContainsString($expectedValue, $model->refresh()->{$field}->body->toHtml());
    }
}
