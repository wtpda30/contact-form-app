<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data): bool
    {
        $request = new IndexContactRequest;

        return Validator::make($data, $request->rules())->passes();
    }

    public function test_正しい検索条件なら通過する(): void
    {
        $category = Category::factory()->create();

        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-06-07',
        ];

        $this->assertTrue($this->validate($data));
    }

    public function test_検索条件は空でも通過する(): void
    {
        $this->assertTrue($this->validate([]));
    }

    public function test_性別が不正ならエラーになる(): void
    {
        $this->assertFalse($this->validate([
            'gender' => 4,
        ]));
    }

    public function test_日付形式が不正ならエラーになる(): void
    {
        $this->assertFalse($this->validate([
            'date' => 'abc',
        ]));
    }

    public function test_存在しないカテゴリidならエラーになる(): void
    {
        $this->assertFalse($this->validate([
            'category_id' => 999,
        ]));
    }
}
