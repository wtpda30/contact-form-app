<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分自身のタグ名は重複扱いにならない(): void
    {
        $tag = Tag::create(['name' => '質問']);

        $request = UpdateTagRequest::create(
            "/admin/tags/{$tag->id}",
            'PUT',
            ['name' => '質問']
        );

        Route::shouldReceive('current')->andReturn(null);

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag) {
                public function __construct(private Tag $tag) {}

                public function parameter($key)
                {
                    return $key === 'tag' ? $this->tag : null;
                }
            };
        });

        $validator = Validator::make(
            ['name' => '質問'],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_他のタグ名と重複するとエラーになる(): void
    {
        Tag::create(['name' => '質問']);

        $tag = Tag::create(['name' => '要望']);

        $request = UpdateTagRequest::create(
            "/admin/tags/{$tag->id}",
            'PUT',
            ['name' => '質問']
        );

        $request->setRouteResolver(function () use ($tag) {
            return new class ($tag) {
                public function __construct(private Tag $tag){}

                public function parameter($key)
                {
                    return $this->tag;
                }
            };
        });

        $validator = Validator::make(
            ['name' => '質問'],
            $request->rules(),
            $request->messages()
        );

        $this->assertFalse($validator->passes());
    }
}
