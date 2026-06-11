<?php

namespace Tests\Unit;

use App\Http\Requests\Api\IndexContactRequest;
use App\Http\Requests\Api\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiContactValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api検索バリデーションが通る(): void
    {
        $category = Category::factory()->create();

        $request = new IndexContactRequest();

        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => now()->toDateString(),
            'per_page' => 10,
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_api検索で性別1から3は通る(): void
    {
        $request = new IndexContactRequest();

        foreach ([1, 2, 3] as $gender) {
            $validator = Validator::make(
                ['gender' => $gender],
                $request->rules(),
                $request->messages()
            );

            $this->assertFalse($validator->fails());
        }
    }

    public function test_api検索で性別が不正ならエラーになる(): void
    {
        $request = new IndexContactRequest();

        $validator = Validator::make(
            ['gender' => 999],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());

        $this->assertSame('性別の値が不正です', $validator->errors()->first('gender'));
    }

    public function test_api検索で存在しないカテゴリーならエラーになる(): void
    {
        $request = new IndexContactRequest();

        $validator = Validator::make(
            ['category_id' => 999],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());

        $this->assertSame('選択されたカテゴリーが存在しません', $validator->errors()->first('category_id'));
    }

    public function test_api検索で不正な日付ならエラーになる(): void
    {
        $request = new IndexContactRequest();

        $validator = Validator::make(
            ['date' => 'abc'],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
    }

    public function test_api検索で不正なper_pageならエラーになる(): void
    {
        $request = new IndexContactRequest();

        $validator = Validator::make(
            ['per_page' => 0],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
    }

    public function test_api作成バリデーションが通る(): void
    {
        $category = Category::factory()->create();

        $tag = Tag::factory()->create();

        $request = new StoreContactRequest();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'API作成テストです',
            'tag_ids' => [$tag->id],
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_api作成で不正な値なら日本語エラーになる(): void
    {
        $request = new StoreContactRequest();

        $data = [
            'first_name' => '',
            'last_name' => '',
            'gender' => 999,
            'email' => 'test',
            'tel' => 'abc',
            'address' => '',
            'category_id' => 999,
            'detail' => '',
            'tag_ids' => [999],
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertSame('姓を入力してください', $validator->errors()->first('first_name'));
        $this->assertSame('名を入力してください', $validator->errors()->first('last_name'));
        $this->assertSame('性別の値が不正です', $validator->errors()->first('gender'));
        $this->assertSame('メールアドレスはメール形式で入力してください', $validator->errors()->first('email'));
        $this->assertSame('電話番号はハイフンなしの10～11桁で入力してください', $validator->errors()->first('tel'));
        $this->assertSame('住所を入力してください', $validator->errors()->first('address'));
        $this->assertSame('選択されたカテゴリーが存在しません', $validator->errors()->first('category_id'));
        $this->assertSame('お問い合わせ内容を入力してください', $validator->errors()->first('detail'));
        $this->assertSame('選択されたタグが存在しません', $validator->errors()->first('tag_ids.0'));
    }

    public function test_api作成でtag_idsが配列でなければエラーになる(): void
    {
        $request = new StoreContactRequest();

        $validator = Validator::make(
            ['tag_ids' => 1],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
    }
}
