<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ContactFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_お問い合わせ入力ページが表示される(): void
    {
        Category::factory()->create(['content' => '商品の交換について']);
        Tag::factory()->create(['name' => '質問']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('contact.index');

        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        
        $response->assertSee('商品の交換について');
        $response->assertSee('質問');
    }

    public function test_確認ページに入力内容が表示される(): void
    {
        $category = Category::factory()->create(['content' => '商品トラブル']);
        $tag = Tag::factory()->create(['name' => '要望']);
        $response = $this->post('/contacts/confirm', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストマンション',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertOk();
        $response->assertViewIs('contact.confirm');
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('test@example.com');
        $response->assertSee('商品トラブル');
        $response->assertSee('要望');
    }

    public function test_お問い合わせ送信でcontactsとcontact_tagに保存される(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $response = $this->post('/contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストマンション',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);

        $contact = Contact::first();

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    public function test_バリデーションエラー時は確認ページへ進まない(): void
    {
        $response = $this->from('/')
            ->post('/contacts/confirm', [
                'first_name' => '',
                'last_name' => '',
                'gender' => '',
                'email' => '',
                'tel' => '',
                'address' => '',
                'category_id' => '',
                'detail' => '',
            ]);

        $response->assertRedirect('/');

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    public function test_お問い合わせ送信時にバリデーションエラーなら保存されない(): void
    {
        $response = $this->from('/')
            ->post('/contacts', [
                'first_name' => '',
                'last_name' => '',
                'gender' => '',
                'email' => '',
                'tel' => '',
                'address' => '',
                'category_id' => '',
                'detail' => '',
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);

    $this->assertDatabaseCount('contacts', 0);
    }

    public function test_thanksページが表示される(): void
    {
        $response = $this->get('/thanks');
        $response->assertOk();
        $response->assertViewIs('contact.thanks');
    }
}
