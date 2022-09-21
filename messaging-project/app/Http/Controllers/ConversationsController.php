<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveMessageRequest;
use App\Repository\ConversationRepository;
use App\Models\User;
use Illuminate\support\Facades\Auth;

class ConversationsController extends Controller
{
    public function index()
    {
        $users = ConversationRepository::getFriends(Auth::user()->id);
        return view('conversations/index', ['users' => $users]);
    }

    public function getConversations(User $user)
    {
        $users = ConversationRepository::getFriends(Auth::user()->id);
        $messages = ConversationRepository::getMessages(Auth::user()->id, $user->id);
        // dd($user->id);
        $url = 'https://messaging-project.test/api/public_key_encryption/' . $user->id;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $public_key_encryption = curl_exec($curl);
        curl_close($curl);

        $url = 'https://messaging-project.test/api/public_key_signature/' . $user->id;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $public_key_signature = curl_exec($curl);
        curl_close($curl);



        return view('conversations/conversation', [
            'users' => $users,
            'user' => $user,
            'messages' => $messages,
            'public_key_encryption' => $public_key_encryption,
            'public_key_signature' => $public_key_signature,
        ]);
    }

    public function createMessage(User $user, SaveMessageRequest $request)
    {
        $users = ConversationRepository::getFriends(Auth::user()->id);
        ConversationRepository::createMessage(
            $request->get('content'),
            $request->get('iv'),
            $request->get('digital_signature'),
            Auth::user()->id,
            $user->id,
        );
        return redirect(route('conversations.show', [
            'users' => $users,
            'user' => $user
        ]));
    }
}
