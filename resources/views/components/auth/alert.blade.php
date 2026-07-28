@if ($errors->any())
    <div class="mb-5 p-3 border border-red-900/30 text-sm text-red-400/90">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@if (session('status'))
    <div class="mb-5 p-3 border border-emerald-900/30 text-sm text-emerald-400/90">
        {{ session('status') }}
    </div>
@endif
