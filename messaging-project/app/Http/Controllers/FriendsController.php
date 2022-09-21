<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveMessageRequest;
use App\Repository\ConversationRepository;
use App\Models\User;
use Illuminate\support\Facades\Auth;

class FriendsController extends Controller
{
    public function getUsers()
    {
        $users = ConversationRepository::getAllUsers(Auth::user()->id);
        return view('friends/search', ['users' => $users]);
    }
    public function getFriends()
    {
        $friends = ConversationRepository::getFriends(Auth::user()->id);
        $friendsPending = ConversationRepository::getFriendsPending(Auth::user()->id);
        return view('friends/friendlist', ['friends' => $friends, 'friendsPending' => $friendsPending]);
    }

    public function addFriend(User $user)
    {
        ConversationRepository::addFriend(Auth::user()->id, $user->id);
        return redirect('friends');
    }

    public function confirmFriend(User $user)
    {
        ConversationRepository::confirmFriend(Auth::user()->id, $user->id);
        return redirect('friends');
    }

    public function rejectFriend(User $user)
    {
        ConversationRepository::rejectFriend(Auth::user()->id, $user->id);
        return redirect('friends');
    }
}
