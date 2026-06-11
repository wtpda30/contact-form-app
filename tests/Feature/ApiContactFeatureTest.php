<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ApiContactFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_api問い合わせ一覧を取得でき検索とページネーションも機能する(): void
    {
        $category = Category::factory()->create();

        $tag = Tag::factory()->create();

        $target = Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'category_id' => $category->id,
            'created_at' => '2026-06-11 10:00:00',
        ]);

        $target->tags()->attach($tag->id);

        Contact::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/contacts?keyword=山田&gender=1&category_id=' . $category->id . '&date=2026-06-11&per_page=1');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'gender',
                        'email',
                        'tel',
                        'address',
                        'building',
                        'detail',
                        'category',
                        'tags',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('data.0.id', $target->id);
    }

    public function test_api問い合わせ一覧はバリデーションエラー時に422を返す(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=999&category_id=999&date=abc&per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'gender',
                'category_id',
                'date',
                'per_page',
            ]);
    }

    public function test_api問い合わせ詳細を取得できる(): void
    {
        $category = Category::factory()->create();

        $tag = Tag::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/contacts/' . $contact->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'gender',
                    'email',
                    'tel',
                    'address',
                    'building',
                    'detail',
                    'category',
                    'tags',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_api問い合わせ詳細は存在しないidなら404を返す(): void
    {
        $response = $this->getJson('/api/v1/contacts/999999');
        $response->assertNotFound();
    }

    public function test_api問い合わせを作成できる(): void
    {
        $category = Category::factory()->create();

        $tag = Tag::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'API作成テストです',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', '太郎')
            ->assertJsonPath('data.category.id', $category->id);

        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        $contact = Contact::where('email', 'taro@example.com')->first();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_api問い合わせ作成はバリデーションエラー時に422を返す(): void
    {
        $data = [
            'first_name' => '',
            'last_name' => '',
            'gender' => 999,
            'email' => 'test',
            'tel' => 'abc',
            'address' => '',
            'building' => '',
            'category_id' => 999,
            'detail' => '',
            'tag_ids' => [999],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'category_id',
                'detail',
                'tag_ids.0',
            ]);
    }

    public function test_api問い合わせを更新できる(): void
    {
        $oldCategory = Category::factory()->create();

        $newCategory = Category::factory()->create();

        $tag = Tag::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $oldCategory->id,
        ]);

        $data = [
            'first_name' => '次郎',
            'last_name' => '山田',
            'gender' => 2,
            'email' => 'jiro@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => '更新ビル202',
            'category_id' => $newCategory->id,
            'detail' => 'API更新テストです',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->putJson('/api/v1/contacts/' . $contact->id, $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonPath('data.first_name', '次郎')
            ->assertJsonPath('data.category.id', $newCategory->id);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '次郎',
            'email' => 'jiro@example.com',
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_api問い合わせ更新は存在しないidなら404を返す(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '次郎',
            'last_name' => '山田',
            'gender' => 2,
            'email' => 'jiro@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => '更新ビル202',
            'category_id' => $category->id,
            'detail' => 'API更新テストです',
            'tag_ids' => [],
        ];

        $response = $this->putJson('/api/v1/contacts/999999', $data);

        $response->assertNotFound();
    }

    public function test_api問い合わせ更新はバリデーションエラー時に422を返す(): void
    {
        $contact = Contact::factory()->create();
        $data = [
            'first_name' => '',
            'last_name' => '',
            'gender' => 999,
            'email' => 'test',
            'tel' => 'abc',
            'address' => '',
            'building' => '',
            'category_id' => 999,
            'detail' => '',
            'tag_ids' => [999],
        ];

        $response = $this->putJson('/api/v1/contacts/' . $contact->id, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'category_id',
                'detail',
                'tag_ids.0',
            ]);
    }

    public function test_api問い合わせを削除できる(): void
    {
        $tag = Tag::factory()->create();

        $contact = Contact::factory()->create();

        $contact->tags()->attach($tag->id);

        $response = $this->deleteJson('/api/v1/contacts/' . $contact->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_api問い合わせ削除は存在しないidなら404を返す(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/999999');
        $response->assertNotFound();
    }
}
