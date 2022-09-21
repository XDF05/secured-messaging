<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\ConversationRepository;
use App\Models\User;
use Illuminate\support\Facades\Auth;

class apiController extends Controller
{
    public function getUserPublicKeyEncryption(User $user)
    {
        $public_key_encryption = ConversationRepository::getUserPublicKeyEncryption($user->id);
        return $public_key_encryption;
    }
    public function getUserPublicKeySignature(User $user)
    {
        $public_key_signature = ConversationRepository::getUserPublicKeySignature($user->id);
        return $public_key_signature;
    }
}
