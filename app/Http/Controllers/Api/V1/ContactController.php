<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexContactRequest;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Requests\Api\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContactRequest $request)
    {
        $validated = $request->validated();

        $query = Contact::with(['category', 'tags']);

        if (!empty($validated['keyword'])) {
            $keyword = $validated['keyword'];

            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if (!empty($validated['gender'])) {
            $query->where('gender', $validated['gender']);
        }

        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (!empty($validated['date'])) {
            $query->whereDate('created_at', $validated['date']);
        }

        $perPage = $validated['per_page'] ?? 7;

        $contacts = $query->latest()->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $contact = DB::transaction(function () use ($validated) {
            $tagIds = $validated['tag_ids'] ?? [];

            unset($validated['tag_ids']);

            $contact = Contact::create($validated);

            $contact->tags()->sync($tagIds);

            return $contact->load(['category', 'tags']);
        });

        return response()->json([
            'data' => new ContactResource($contact),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        return new ContactResource($contact->load(['category', 'tags']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $contact) {
            $tagIds = $validated['tag_ids'] ?? [];

            unset($validated['tag_ids']);

            $contact->update($validated);
            $contact->tags()->sync($tagIds);
        });

        return new ContactResource($contact->load(['category','tags']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->tags()->detach();
        $contact->delete();
        return response()->json(null, 204);
    }
}
