<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contactはcategoryに属する(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertEquals($category->id, $contact->category->id);
    }

    public function test_contactは複数のtagを持てる(): void
    {
        $contact = Contact::factory()->create();

        $tags = Tag::factory()->count(2)->create();

        $contact->tags()->attach($tags->pluck('id')->toArray());

        $this->assertCount(2, $contact->tags);
    }
}
