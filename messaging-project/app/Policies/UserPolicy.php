<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Repository\ConversationRepository;

class UserPolicy
{
    use HandlesAuthorization;

    public function talkTo(User $user, User $to)
    {
        $friendList = ConversationRepository::getFriends($user->id);
        foreach ($friendList as $friend) {
            if ($friend->id == $to->id && $user->id !== $to->id) {
                return true;
            }
        }
        return false;
    }
}
