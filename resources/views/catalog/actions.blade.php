<div class="row-actions">
    @unless (request()->routeIs("web.$key.show"))
        <a aria-label="Переглянути запис {{ $item->id }}" href="{{ route("web.$key.show", $item->id) }}">Переглянути</a>
    @endunless
    @can('update', $item)
        <a aria-label="Редагувати запис {{ $item->id }}" href="{{ route("web.$key.edit", $item->id) }}">Редагувати</a>
    @endcan
    @can('delete', $item)
        @unless ($key === 'users' && $item->id === auth()->id())
            <form method="POST" action="{{ route("web.$key.destroy", $item->id) }}" onsubmit="return confirm('Видалити цей запис? Залежні записи можуть бути видалені разом із ним.');">
                @csrf
                @method('DELETE')
                <button class="btn btn-link delete-link" aria-label="Видалити запис {{ $item->id }}">Видалити</button>
            </form>
        @endunless
    @endcan
</div>
