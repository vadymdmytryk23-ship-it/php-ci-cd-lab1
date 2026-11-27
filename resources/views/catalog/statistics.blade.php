@extends('layouts.app')
@section('title', 'Статистика')
@section('content')
    <h1>Статистика</h1>
    <div class="sheet editor">
        <dl class="detail-list">
            <dt>Користувачі</dt><dd>{{ $users }}</dd>
            <dt>Несплачені нарахування</dt><dd>{{ $bills }}</dd>
            <dt>Сплачено в системі</dt><dd>{{ number_format($paid, 2, ',', ' ') }} грн</dd>
        </dl>
    </div>
@endsection
