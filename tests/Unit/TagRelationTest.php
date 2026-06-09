<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tagは複数のcontactを持てる(): void
    {
        $tag = Tag::factory()->create();

        $contacts = Contact::factory()->count(2)->create();

        $tag->contacts()->attach($contacts->pluck('id')->toArray());

        $this->assertCount(2, $tag->contacts);
    }
}
