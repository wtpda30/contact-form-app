<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data): bool
    {
        $request = new StoreTagRequest();

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    public function test_タグ名が正しければ通過する(): void
    {
        $this->assertTrue($this->validate([
            'name' => '質問',
        ]));
    }

    public function test_タグ名が空ならエラーになる(): void
    {
        $this->assertFalse($this->validate([
            'name' => '',
        ]));
    }

    public function test_タグ名が50文字を超えるとエラーになる(): void
    {
        $this->assertFalse($this->validate([
            'name' => str_repeat('あ', 51),
        ]));
    }

    public function test_重複したタグ名はエラーになる(): void
    {
        Tag::create(['name' => '質問']);

        $this->assertFalse($this->validate([
            'name' => '質問',
        ]));
    }
}
