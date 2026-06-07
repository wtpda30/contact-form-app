<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Route;

Route::get('/',[ContactController::class,'index'])->name('contact.index');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');
Route::post('/contacts', [ContactController::class, 'store'])->name('contact.store');
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

Route::middleware('auth')->group(function () {
    Route::post('/admin/tags', [TagController::class,'store']);
    Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit']);
    Route::put('/admin/tags/{tag}', [TagController::class, 'update']);
    Route::delete('/admin/tags/{tag}', [TagController::class, 'destroy']);
});

//仮ルート:コントローラー作ってから書き換え
Route::middleware('auth')->group(function () {
    Route::get('/admin', function () {
        return view('admin.index', [
            'contacts' => Contact::with(['category', 'tags'])->paginate(7),
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    })->name('admin.index');
});
