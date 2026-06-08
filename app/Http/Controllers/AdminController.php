<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContactRequest $request)
    {
        $validated = $request->validated();

        $query = Contact::with(['category', 'tags']);

        if(!empty($validated['keyword'])) {
                $keyword=$validated['keyword'];
                $query->where(function ($query) use ($keyword) {
                    $query->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
        }
        if(!empty($validated['gender']) && $validated['gender'] != 0){
                $query->where('gender', $validated['gender']);
        }
        if(!empty($validated['category_id'])) {
                $query->where('category_id', $validated['category_id']);
        }
        if(!empty($validated['date'])) {
                $query->whereDate('created_at', $validated['date']);
        }
        $contacts=$query->latest()->paginate(7);

        $categories = Category::all();

        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load(['category','tags']);

        return view('admin.show', compact('contact'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}
