<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeatureTest extends TestCase
{
    use RefreshDatabase;
    public function test_未ログインなら管理画面にアクセスできない(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_ログイン済みなら管理画面が表示される(): void
    {
        $user = User::factory()->create();
        Category::factory()->create();
        Tag::factory()->create();
        Contact::factory()->create();

        $response = $this->actingAs($user)->get('/admin');
        $response->assertOk();
        $response->assertViewIs('admin.index');
    }

    public function test_管理画面で検索できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'gender' => 1,
            'category_id' => $category->id,
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
            'gender' => 2,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?keyword=山田');
        $response->assertOk();
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_お問い合わせ詳細ページが表示される(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['content' => '商品トラブル']);
        $tag = Tag::factory()->create(['name' => '質問']);

        $contact = Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($tag->id);
        $response = $this->actingAs($user)->get("/admin/contacts/{$contact->id}");
        $response->assertOk();
        $response->assertViewIs('admin.show');
        $response->assertSee('山田');
        $response->assertSee('商品トラブル');
        $response->assertSee('質問');
    }

    public function test_お問い合わせを削除できる(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_管理画面で性別検索できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'first_name' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'gender' => 2,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?gender=1');

        $response->assertOk();
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_管理画面でカテゴリ検索できる(): void
    {
        $user = User::factory()->create();

        $category1 = Category::factory()->create(['content' => '商品トラブル']);
        $category2 = Category::factory()->create(['content' => 'その他']);

        Contact::factory()->create([
            'first_name' => '山田',
            'category_id' => $category1->id,
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'category_id' => $category2->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?category_id=' . $category1->id);

        $response->assertOk();
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_管理画面で日付検索できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'first_name' => '山田',
            'email'=>'yamada@example.com',
            'category_id' => $category->id,
            'created_at' => '2026-06-09 10:00:00',
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'email'=>'sato@example.com',
            'category_id' => $category->id,
            'created_at' => '2026-06-08 10:00:00',
        ]);

        $response = $this->actingAs($user)->get('/admin?date=2026-06-09');

        $response->assertOk();
        $response->assertSee('yamada@example.com');
        $response->assertDontSee('sato@example.com');
    }

    public function test_管理画面は7件ごとにページネーションされる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->count(8)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertViewHas('contacts');

        $contacts = $response->viewData('contacts');
        $this->assertEquals(7, $contacts->perPage());
        $this->assertEquals(8, $contacts->total());
    }
}
