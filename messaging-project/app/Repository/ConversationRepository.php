<?php

namespace App\Repository;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConversationRepository
{

    public static function getAllUsers(int $user_id)
    {
        $users = DB::table('users')->where('id', '!=', $user_id)->get();
        return $users;
    }
    public static function getFriends(int $user_id)
    {
        $sql_friends_sent = DB::table('friendships')
            ->join('users', 'users.id', '=', 'friendships.to_id')
            ->where('friendships.status', '=', 'confirmed')
            ->where('friendships.from_id', '=', $user_id);

        $friends = DB::table('friendships')
            ->join('users', 'users.id', '=', 'friendships.from_id')
            ->where('friendships.status', '=', 'confirmed')
            ->where('friendships.to_id', '=', $user_id)
            ->union($sql_friends_sent)
            ->get();

        return $friends;
    }

    public static function getFriendsPending(int $user_id)
    {
        $friends = DB::table('friendships')
            ->join('users', 'users.id', '=', 'friendships.from_id')
            ->where('friendships.status', '=', 'pending')
            ->where('friendships.to_id', '=', $user_id)
            ->get();

        return $friends;
    }

    public static function addFriend(int $from, int $to)
    {
        $sql = "INSERT INTO friendships (from_id, to_id, status, created_at)"
            . " VALUES (?,?,?,?)";
        DB::insert($sql, [$from, $to, 'pending', Carbon::now()]);
    }

    public static function confirmFriend(int $from, int $to)
    {
        DB::table('friendships')
            ->where('from_id', '=', $to)
            ->where('to_id', '=', $from)
            ->update(['status' => 'confirmed', 'created_at' => Carbon::now()]);
    }

    public static function rejectFriend(int $from, int $to)
    {
        DB::table('friendships')
            ->where('from_id', '=', $to)
            ->where('to_id', '=', $from)
            ->delete();
    }

    public static function getMessages(int $from_uid, int $to_uid)
    {

        $sql = "SELECT * FROM messages"
            . " JOIN users on messages.from_id = users.id"
            . " WHERE ((messages.from_id = ? AND messages.to_id = ?) OR (messages.from_id = ? AND messages.to_id = ?))"
            . "ORDER BY messages.created_at ASC";

        $messages = DB::select($sql, [$from_uid, $to_uid, $to_uid, $from_uid]);
        return $messages;
    }

    public static function createMessage(string $content, string $iv, string $digital_signature, int $from, int $to)
    {
        $sql = "INSERT INTO messages (content, iv, digital_signature ,from_id, to_id, created_at)"
            . " VALUES (?,?,?,?,?,?)";
        DB::insert($sql, [$content, $iv, $digital_signature, $from, $to, Carbon::now()]);
    }

    public static function getUserPublicKeyEncryption(int $to_id)
    {
        $public_key_encryption = DB::table('users')
            ->where('id', '=', $to_id)
            ->get(['public_key_encryption']);
        return $public_key_encryption;
    }

    public static function getUserPublicKeySignature(int $to_id)
    {
        $public_key_signature = DB::table('users')
            ->where('id', '=', $to_id)
            ->get(['public_key_signature']);
        return $public_key_signature;
    }
}
