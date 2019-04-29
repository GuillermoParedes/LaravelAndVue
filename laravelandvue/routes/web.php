<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'pusher', 'middleware' => ['auth']], function () 
{
    // Ruta para crear un nuevo post
    Route::post('posts/{id}', function ($id, \Illuminate\Http\Request $request) 
    { 
        $comment = new \App\Comment([
            'comment' => $request->input('comment'),
            'user_id' => auth()->user()->id,
            'post_id' => $id
        ]);
        $comment->save();
        broadcast(new \App\Events\FireComment($comment))->toOthers();
    })->name('comments.create');
    // Ruta para mostrar el post especifico
    Route::get('posts/{id}', function ($id) 
    {
        $post = \App\Post::findOrFail($id);
        return view('chat', compact('post'));
    });

    // Ruta para mostrar los comentarios de un post especifico
    Route::get('comments/{id}', function ($id) 
    {
        $comments = \App\Comment::whre('post_id', $id)->with('user')->get();
        return response()->json($comments);
    })->name('comments.list');
});