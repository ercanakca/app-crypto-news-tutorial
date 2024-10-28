<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('news') }}" class="p-6">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="col-span-1">
                                <label for="coins" class="block text-sm font-medium text-gray-700">{{ __('Coin Seçin:') }}</label>
                                <select name="coins[]" id="coins" multiple class="border border-gray-300 rounded-md p-2 w-full">
                                    @foreach ($coins as $coin)
                                        <option value="{{ $coin['id'] }}"
                                            {{ in_array($coin['id'], old('coins', $selectedCoins ?? [])) ? 'selected' : '' }}>
                                            {{ $coin['code'] . ' - ' . $coin['title'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-1">
                                <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Başlangıç Tarihi:') }}</label>
                                <input type="date" name="start_date" id="start_date"
                                       class="border border-gray-300 rounded-md p-2 w-full"
                                       value="{{ old('start_date', $startDate ?? '') }}">
                            </div>

                            <div class="col-span-1">
                                <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('Bitiş Tarihi:') }}</label>
                                <input type="date" name="end_date" id="end_date"
                                       class="border border-gray-300 rounded-md p-2 w-full"
                                       value="{{ old('end_date', $endDate ?? '') }}">
                            </div>

                            <div class="col-span-1 items-end">
                                <br>
                                <button type="submit"
                                        class="bg-blue-500 text-white px-4 py-2 rounded w-full hover:bg-blue-600 transition">
                                    {{ __('Filtrele') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <br>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow sm:rounded-lg">


                <div class="overflow-x-auto">
                    <table class="table table-bordered min-w-full divide-y divide-gray-200 mt-4">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('') }}Başlık</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('') }}Coin's</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('') }}Yayın Tarihi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('') }}Kaynak</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('') }}Tür</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($news as $newsItem)
                            <tr>
                                <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4">{{ $newsItem['title'] }}</td>
                                <td class="px-6 py-4">{{ $newsItem['coin_found'] }}</td>
                                <td class="px-6 py-4">{{ $newsItem['published_at'] }}</td>
                                <td class="px-6 py-4">{{ $newsItem['source_title'] }}</td>
                                <td class="px-6 py-4">{{ $newsItem['kind'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">{{ __('Haber bulunamadı.') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($news instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-4">
                        {{ $news->appends(request()->input())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
