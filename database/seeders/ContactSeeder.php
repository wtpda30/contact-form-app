<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Factoryを使って20件のContactを作成、1件ずつタグを紐付ける
        Contact::factory()
            ->count(20)
            ->create()
            ->each(function ($contact) {
                // 1〜3件のランダムな数を決定、ランダムなタグのIDを取得
                $tagIds = Tag::inRandomOrder()
                    ->limit(rand(1, 3))
                    ->pluck('id');

                // 中間テーブルに紐付け
                $contact->tags()->attach($tagIds);
            });
    }
}
