<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;
    private function validate(array $data): bool
    {
        $request= new StoreContactRequest();

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    public function test_正しい入力内容ならバリデーションを通過する(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $data = [
            'first_name'=>'山田',
            'last_name'=>'太郎',
            'gender'=>1,
            'email'=>'test@example.com',
            'tel'=>'09012345678',
            'address'=>'東京都渋谷区',
            'building'=>'テストマンション',
            'category_id'=>$category->id,
            'detail'=>'お問い合わせ内容です',
            'tag_ids'=> $tags->pluck('id')->toArray(),
        ];

        $this->assertTrue($this->validate($data));
    }

    public function test_必須項目が空ならバリデーションエラーになる(): void
    {
        $data = [
            'first_name'=>'',
            'last_name'=>'',
            'gender'=>'',
            'email'=>'',
            'tel'=>'',
            'address'=>'',
            'category_id'=>'',
            'detail'=>'',
        ];

        $this->assertFalse($this->validate($data));
    }

    public function test_電話番号が10桁未満ならエラーになる(): void
    {
        $category = Category::factory()->create();
        $data = [
            'first_name'=>'山田',
            'last_name'=>'太郎',
            'gender'=>1,
            'email'=>'test@example.com',
            'tel'=>'090123456',
            'address'=>'東京都渋谷区',
            'category_id'=>$category->id,
            'detail'=>'お問い合わせ内容です',
        ];

        $this->assertFalse($this->validate($data));
    }

    public function test_お問い合わせ内容が120文字を超えるとエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name'=>'山田',
            'last_name'=>'太郎',
            'gender'=>1,
            'email'=>'test@example.com',
            'tel'=>'09012345678',
            'address'=>'東京都渋谷区',
            'category_id'=>$category->id,
            'detail'=>str_repeat('あ',121),
        ];

        $this->assertFalse($this->validate($data));
    }
}
