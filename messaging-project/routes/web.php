<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConversationsController;
use App\Http\Controllers\FriendsController;

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

Route::redirect('/', '/register');


Route::get('/home', [ConversationsController::class, 'index'])
    ->middleware(['auth'])
    ->name('home');

Route::get('/conversations', [ConversationsController::class, 'index'])
    ->name('conversations');

Route::get('/conversations/{user}', [ConversationsController::class, 'getConversations'])
    ->middleware('can:talkTo,user')
    ->name('conversations.show');

Route::post('/conversations/{user}', [ConversationsController::class, 'createMessage'])
    ->middleware('can:talkTo,user');

Route::get('/users', [FriendsController::class, 'getUsers'])
    ->name('users.search');

Route::get('/friends', [FriendsController::class, 'getFriends'])
    ->middleware(['auth'])
    ->name('friends.list');

Route::get('/friends/add/{user}', [FriendsController::class, 'addFriend'])
    ->middleware(['auth'])
    ->name('friends.add');

Route::get('/friends/confirm/{user}', [FriendsController::class, 'confirmFriend'])
    ->middleware(['auth'])
    ->name('friends.confirm');

Route::get('/friends/reject/{user}', [FriendsController::class, 'rejectFriend'])
    ->middleware(['auth'])
    ->name('friends.reject');

require __DIR__ . '/auth.php';
