@extends('conversations.template')
@section('title', 'home')
@section('content')
    @include('conversations.users', ['users' => $users])
@endsection
