<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            店舗情報
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">
                    {{-- トースト・フラッシュメッセージの表示 --}}
                    <x-flash-message status="session('status')" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        @foreach ($shops as $shop)
                            <div class="min-w-0">
                                <a href="{{ route('owner.shops.edit', ['shop' => $shop->id]) }}" class="block h-full min-w-0">
                                    <div class="border border-gray-200 rounded-md p-3 sm:p-4 h-full hover:border-indigo-200 transition-colors">
                                        <div class="mb-3 sm:mb-4">
                                            @if ($shop->is_selling)
                                                <span class="inline-block border border-transparent rounded-md px-2 py-1 text-xs sm:text-sm bg-blue-400 text-white">販売中</span>
                                            @else
                                                <span class="inline-block border border-transparent rounded-md px-2 py-1 text-xs sm:text-sm bg-red-400 text-white">停止中</span>
                                            @endif
                                        </div>
                                        <div class="text-base sm:text-lg md:text-xl font-medium text-gray-900 break-words mb-2 sm:mb-3">{{ $shop->name }}</div>
                                        <x-thumbnail :filename="$shop->filename" type="shops" />
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
