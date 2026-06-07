<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories=Category::all();
        $tags=Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function confirm(StoreContactRequest $request)
    {
        $validated=$request->validated();

        $category=Category::find($validated['category_id']);

        $tags = collect();
        if(!empty($validated['tag_ids'])){
            $tags = Tag::whereIn('id', $validated['tag_ids'])->get();
        }

        return view('contact.confirm',compact('validated','category','tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $validated=$request->validated();

        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        DB::transaction(function () use ($validated, $tagIds) {
            $contact = Contact::create($validated);

            if(!empty($tagIds)){
            $contact->tags()->attach($tagIds);
            }
        });

        return redirect('/thanks');
    }

    /**
     * Display the specified resource.
     */
    public function thanks()
    {
        return view('contact.thanks');
    }

}
