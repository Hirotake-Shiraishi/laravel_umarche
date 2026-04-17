<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight break-words">
            商品管理
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">
                    {{-- トースト・フラッシュメッセージの表示 --}}
                    <x-flash-message status="session('status')" />
                    <div class="flex justify-stretch sm:justify-end mb-4">
                        <button type="button" onclick="location.href='{{ route('owner.products.create') }}'"
                            class="w-full sm:w-auto text-white bg-indigo-500 border-0 py-2 px-4 sm:px-8 focus:outline-none hover:bg-indigo-600 rounded-md text-sm sm:text-lg">新規登録</button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-4">
                        @foreach ($products as $product)
                            <div class="min-w-0">
                                <a href="{{ route('owner.products.edit', ['product' => $product->id]) }}" class="block h-full min-w-0">
                                    <div class="border border-gray-200 rounded-md p-2 sm:p-4 h-full hover:border-indigo-200 transition-colors">
                                        {{-- <x-thumbnail :filename="$product->imageFirst->filename" type="products" /> --}}
                                        <x-thumbnail filename="{{ $product->imageFirst->filename ?? '' }}" type="products" />
                                        <div class="mt-2 text-sm sm:text-base text-gray-900 font-medium break-words">{{ $product->name }}</div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    {{-- {{ $images->links() }} --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
