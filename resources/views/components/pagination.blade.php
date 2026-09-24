@props([
    'paginator',
    'options' => [10, 25, 50, 75, 100, 250, 500],
    'perPage' => null,
])

@php
    $currentPerPage = (int) ($perPage ?? request()->input('per_page', $paginator->perPage() ?? 25));
    if (!in_array($currentPerPage, $options, true)) {
        $options[] = $currentPerPage;
        sort($options);
    }
    $pageName = method_exists($paginator, 'getPageName') ? $paginator->getPageName() : 'page';
    $queryParams = request()->except(['per_page', 'page', $pageName]);
@endphp

<div class="p-4 border-t border-ocean-100 dark:border-ocean-900/60 bg-ocean-50/50 dark:bg-ocean-950/40 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    {{-- Dropdown Baris Data (10, 25, 50, 75, 100, 250, 500) --}}
    <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
        <span>{{ __('Tampilkan') }}</span>
        <form method="GET" action="{{ url()->current() }}" class="inline-flex items-center m-0">
            @foreach($queryParams as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @elseif($val !== null && $val !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach

            <select name="per_page"
                    onchange="this.form.submit()"
                    aria-label="{{ __('Tampilkan baris per halaman') }}"
                    class="py-1 px-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 cursor-pointer shadow-xs">
                @foreach($options as $option)
                    <option value="{{ $option }}" {{ $currentPerPage == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </form>
        <span>{{ __('baris per halaman') }}</span>
    </div>

    {{-- Pagination Links / Info --}}
    @if($paginator->hasPages())
        <div class="flex-1 md:flex-initial">
            {{ $paginator->links() }}
        </div>
    @elseif($paginator->total() > 0)
        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            {{ __('Menampilkan') }}
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $paginator->firstItem() ?? 1 }}</span>
            {{ __('sampai') }}
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $paginator->lastItem() ?? $paginator->total() }}</span>
            {{ __('dari') }}
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $paginator->total() }}</span>
            {{ __('data') }}
        </div>
    @else
        <div class="text-xs sm:text-sm text-gray-400 dark:text-gray-500">
            {{ __('Menampilkan 0 data') }}
        </div>
    @endif
</div>

