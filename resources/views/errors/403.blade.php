@extends('layouts.app')
@section('title', '403 Доступ заборонено')

@section('content')
    <h1>403 Доступ заборонено</h1>
    <p>У вас немає дозволу виконати цю дію.</p>
    <a class="btn btn-primary" href="{{ route('web.utility-accounts.index') }}">До особових рахунків</a>
@endsection
