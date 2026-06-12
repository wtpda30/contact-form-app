<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログイン済みならタグを作成できる(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->post('/admin/tags', [
                'name' => '質問',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'name' => '質問',
        ]);
    }

    public function test_タグ編集ページが表示される(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($user)
            ->get("/admin/tags/{$tag->id}/edit");

        $response->assertOk();
        $response->assertViewIs('admin.tags.edit');
        $response->assertSee('質問');
    }

    public function test_タグを更新できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($user)
            ->put("/admin/tags/{$tag->id}", [
                'name' => '要望',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '要望',
        ]);
    }

    public function test_タグを削除できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($user)
            ->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_未ログインならタグ操作できない(): void
    {
        $tag = Tag::factory()->create();

        $this->post('/admin/tags', ['name' => '質問'])
            ->assertRedirect('/login');

        $this->get("/admin/tags/{$tag->id}/edit")
            ->assertRedirect('/login');

        $this->put("/admin/tags/{$tag->id}", ['name' => '要望'])
            ->assertRedirect('/login');

        $this->delete("/admin/tags/{$tag->id}")
            ->assertRedirect('/login');
    }
}
