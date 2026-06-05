<?php

use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect() -> route('login');
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
