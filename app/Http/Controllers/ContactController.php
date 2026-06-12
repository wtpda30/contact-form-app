<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $category = Category::find($validated['category_id']);

        $tags = collect();
        if (! empty($validated['tag_ids'])) {
            $tags = Tag::whereIn('id', $validated['tag_ids'])->get();
        }

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        DB::transaction(function () use ($validated, $tagIds) {
            $contact = Contact::create($validated);

            if (! empty($tagIds)) {
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

    public function export(ExportContactRequest $request): StreamedResponse
    {
        $validated = $request->validated();

        $query = Contact::with(['category', 'tags']);

        if (! empty($validated['keyword'])) {
            $keyword = $validated['keyword'];

            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if (! empty($validated['gender']) && $validated['gender'] != 0) {
            $query->where('gender', $validated['gender']);
        }

        if (! empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (! empty($validated['date'])) {
            $query->whereDate('created_at', $validated['date']);
        }

        $contacts = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts.csv"',
        ];

        return response()->stream(function () use ($contacts) {
            $stream = fopen('php://output', 'w');

            // Excel文字化け対策
            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, [
                'ID',
                '氏名',
                '性別',
                'メール',
                '電話',
                '住所',
                '建物',
                'カテゴリ',
                '内容',
                '作成日時',
            ]);

            foreach ($contacts as $contact) {
                fputcsv($stream, [
                    $contact->id,
                    $contact->last_name.' '.$contact->first_name,
                    match ($contact->gender) {
                        1 => '男性',
                        2 => '女性',
                        3 => 'その他',
                        default => '',
                    },
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category->content,
                    $contact->detail,
                    $contact->created_at,
                ]);
            }

            fclose($stream);
        }, 200, $headers);
    }
}
