<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            カート
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">
                    @if(count($products) > 0)
                        @foreach($products as $product)
                            <div class="flex flex-col gap-3 pb-4 mb-4 border-b border-gray-200 last:border-b-0 last:mb-0 last:pb-0 md:flex-row md:items-center md:gap-4 md:pb-2 md:mb-2 md:border-b-0">
                                <div class="w-full max-w-[12rem] mx-auto shrink-0 md:mx-0 md:w-3/12">
                                    @if ($product->imageFirst->filename !== null)
                                        <img src="{{ asset('storage/products/' . $product->imageFirst->filename) }}"
                                            alt=""
                                            class="w-full h-auto max-h-48 object-contain rounded-md bg-gray-50 border border-gray-100">
                                    @else
                                        <div class="aspect-square w-full rounded-md bg-gray-100 border border-gray-200" aria-hidden="true"></div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1 md:w-4/12 text-sm sm:text-base font-medium text-gray-900 break-words">{{ $product->name }}</div>
                                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-sm sm:text-base md:w-3/12 md:justify-around">
                                    <div class="whitespace-nowrap">{{ $product->pivot->quantity }}個</div>
                                    <div class="whitespace-nowrap">{{ number_format($product->pivot->quantity * $product->price) }}<span class="text-xs sm:text-sm text-gray-700">円(税込)</span></div>
                                </div>
                                <div class="flex justify-end md:justify-center md:w-2/12 shrink-0">
                                    <form method="post" action="{{ route('user.cart.delete', ['item' => $product->id ]) }}">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200" aria-label="カートから削除">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                        <div class="my-4 text-sm sm:text-base text-right break-words">
                            小計: {{ number_format($totalPrice) }}<span class="text-xs sm:text-sm text-gray-700">円(税込)</span>
                        </div>
                        <div class="flex justify-stretch sm:justify-end">
                            <button type="button" onclick="location.href='{{ route('user.cart.checkout') }}'"
                                class="w-full sm:w-auto text-white bg-indigo-500 border-0 py-2.5 px-6 text-sm sm:text-base focus:outline-none hover:bg-indigo-600 rounded-md">購入する</button>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">カートに商品が入っていません。</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
