<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tonysm\RichTextLaravel\RichTextLaravel;
use Workbench\App\Models\EncryptedMessage;
use Workbench\App\Models\Message;

class EncryptedModelTest extends TestCase
{
    #[Test]
    public function encrypt_content_based_on_encrypted_option_at_declaration_time(): void
    {
        $encryptedMessage = EncryptedMessage::create(['content' => 'Hello World']);
        $this->assertEncryptedRichTextAttribute($encryptedMessage, 'content', 'Hello World');

        $clearMessage = Message::create(['content' => 'Hello World']);
        $this->assertNotEncryptedRichTextAttribute($clearMessage, 'content', 'Hello World');
    }

    #[Test]
    public function can_clear_encrypted_content_with_null(): void
    {
        $encryptedMessage = EncryptedMessage::create(['content' => 'Hello World']);

        $encryptedMessage->update(['content' => null]);

        $this->assertEncryptedRichTextAttribute($encryptedMessage->refresh(), 'content', null);
    }

    private function assertEncryptedRichTextAttribute($model, string $field, ?string $expectedValue): void
    {
        $encrypted = DB::table('rich_texts')->where('record_id', $model->id)->value('body');

        if (is_null($expectedValue)) {
            $this->assertNotNull($encrypted);
            $this->assertEquals("\n", RichTextLaravel::decrypt($encrypted, $model, $field));
            $this->assertEquals('', $model->refresh()->{$field}->body->toHtml());
        } else {
            $this->assertStringNotContainsString($expectedValue, $encrypted);
            $this->assertEquals($expectedValue, RichTextLaravel::decrypt($encrypted, $model, $field));
            $this->assertStringContainsString($expectedValue, $model->refresh()->{$field}->body->toHtml());
        }
    }

    public function assertNotEncryptedRichTextAttribute($model, $field, string $expectedValue): void
    {
        $this->assertStringContainsString($expectedValue, DB::table('rich_texts')->where('record_id', $model->id)->value('body'));
        $this->assertStringContainsString($expectedValue, $model->refresh()->{$field}->body->toHtml());
    }
}
