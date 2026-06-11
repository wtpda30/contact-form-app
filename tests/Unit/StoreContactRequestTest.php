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

    private function validator(array $data)
    {
        $request = new StoreContactRequest();

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_正しい入力内容ならバリデーションを通過する(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストマンション',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids'=>$tags->pluck('id')->toArray(),
        ];
        $validator = $this->validator($data);
        $this->assertFalse($validator->fails());
    }

    public function test_必須項目が空なら要件通りのエラーになる(): void
    {
        $data = [
            'first_name' => '',
            'last_name' => '',
            'gender' => '',
            'email' => '',
            'tel' => '',
            'address' => '',
            'category_id' => '',
            'detail' => '',
        ];

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertSame('姓を入力してください', $validator->errors()->first('first_name'));
        $this->assertSame('名を入力してください', $validator->errors()->first('last_name'));
        $this->assertSame('性別を選択してください', $validator->errors()->first('gender'));
        $this->assertSame('メールアドレスを入力してください', $validator->errors()->first('email'));
        $this->assertSame('電話番号を入力してください', $validator->errors()->first('tel'));
        $this->assertSame('住所を入力してください', $validator->errors()->first('address'));
        $this->assertSame('お問い合わせの種類を選択してください', $validator->errors()->first('category_id'));
        $this->assertSame('お問い合わせ内容を入力してください', $validator->errors()->first('detail'));
    }

    public function test_メール形式でなければエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ];

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertSame('メールアドレスはメール形式で入力してください', $validator->errors()->first('email'));
    }

    public function test_電話番号が10桁未満ならエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090123456',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ];

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue( $validator->errors()->has('tel'));
    }

    public function test_お問い合わせ内容が120文字を超えるとエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 121),
        ];

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertSame('お問い合わせ内容は120文字以内で入力してください', $validator->errors()->first('detail'));
    }
}
