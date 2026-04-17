<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            商品一覧
        </h2>
        <form method="get" action="{{ route('user.items.index')}}" class="w-full min-w-0 max-w-full mt-2 sm:mt-3">
            <div class="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-end lg:justify-between xl:justify-around w-full min-w-0">
                <div class="flex flex-col gap-2 w-full min-w-0 lg:flex-1 lg:max-w-3xl">
                    <select name="category" class="w-full min-w-0 border border-gray-300 rounded-md py-2 px-2 text-sm sm:text-base bg-white">
                        <option value="0" @if(\Request::get('category') === '0') selected @endif>すべてのカテゴリー</option>
                        @foreach ($categories as $category)
                            <optgroup label="{{ $category->name }}">
                                @foreach ($category->secondary as $secondary)
                                    <option value="{{ $secondary->id }}" @if(\Request::get('category') == $secondary->id) selected @endif>
                                        {{ $secondary->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-stretch w-full min-w-0">
                        <input type="search" name="keyword" value="{{ \Request::get('keyword') }}" class="w-full min-w-0 flex-1 border border-gray-500 rounded-md py-2 px-3 text-sm sm:text-base" placeholder="キーワードを入力" autocomplete="off">
                        <button type="submit" class="w-full sm:w-auto shrink-0 text-white bg-indigo-500 border-0 py-2 px-4 sm:px-6 text-sm sm:text-base focus:outline-none hover:bg-indigo-600 rounded-md">検索する</button>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4 w-full min-w-0 lg:w-auto shrink-0">
                    <div class="w-full sm:w-40 sm:flex-shrink-0 min-w-0">
                        <span class="text-xs sm:text-sm text-gray-600">表示順</span>
                        <select id="sort" name="sort" class="mt-1 w-full min-w-0 border border-gray-300 rounded-md py-2 px-2 text-sm sm:text-base bg-white">
                            <option value="{{ \Constant::SORT_ORDER['recommend']}}"
                                @if(\Request::get('sort') === \Constant::SORT_ORDER['recommend'] )
                                selected
                                @endif>おすすめ順
                            </option>
                            <option value="{{ \Constant::SORT_ORDER['higherPrice']}}"
                                @if(\Request::get('sort') === \Constant::SORT_ORDER['higherPrice'] )
                                selected
                                @endif>料金の高い順
                            </option>
                            <option value="{{ \Constant::SORT_ORDER['lowerPrice']}}"
                                @if(\Request::get('sort') === \Constant::SORT_ORDER['lowerPrice'] )
                                selected
                                @endif>料金の安い順
                            </option>
                            <option value="{{ \Constant::SORT_ORDER['later']}}"
                                @if(\Request::get('sort') === \Constant::SORT_ORDER['later'] )
                                selected
                                @endif>新しい順
                            </option>
                            <option value="{{ \Constant::SORT_ORDER['older']}}"
                                @if(\Request::get('sort') === \Constant::SORT_ORDER['older'] )
                                selected
                                @endif>古い順
                            </option>
                        </select>
                    </div>
                    <div class="w-full sm:w-32 sm:flex-shrink-0 min-w-0">
                        <span class="text-xs sm:text-sm text-gray-600">表示件数</span>
                        <select id="pagination" name="pagination" class="mt-1 w-full min-w-0 border border-gray-300 rounded-md py-2 px-2 text-sm sm:text-base bg-white">
                            <option value="20"
                                @if(\Request::get('pagination') === '20')
                                selected
                                @endif>20件
                            </option>
                            <option value="50"
                                @if(\Request::get('pagination') === '50')
                                selected
                                @endif>50件
                            </option>
                            <option value="100"
                                @if(\Request::get('pagination') === '100')
                                selected
                                @endif>100件
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">
                    {{-- トースト・フラッシュメッセージの表示 --}}
                    <x-flash-message status="session('status')" />
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-4">
                        @foreach ($products as $product)
                            <div class="min-w-0">
                                <a href="{{ route('user.items.show', ['item' => $product->id]) }}" class="block h-full min-w-0">
                                    <div class="border border-gray-200 rounded-md p-2 sm:p-4 h-full hover:border-indigo-200 transition-colors">
                                        {{-- <x-thumbnail :filename="$product->imageFirst->filename" type="products" /> --}}
                                        <x-thumbnail filename="{{ $product->filename ?? '' }}" type="products" />
                                        <div class="mt-2 sm:mt-4 min-w-0">
                                            <h3 class="text-gray-500 text-xs tracking-widest title-font mb-1 break-words">{{ $product->category }}</h3>
                                            <h2 class="text-gray-900 title-font text-sm sm:text-lg font-medium break-words">{{ $product->name }}</h2>
                                            <p class="mt-1 text-sm sm:text-base break-words">{{ number_format($product->price) }}<span class="text-xs sm:text-sm text-gray-700">円(税込)</span></p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        {{ $products->appends([
                            'sort' => \Request::get('sort'),
                            'pagination' => \Request::get('pagination'),
                            'category' => \Request::get('category'),
                            'keyword' => \Request::get('keyword'),
                        ])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    const select = document.getElementById('sort')
    select.addEventListener('change', function(){
        this.form.submit()
    })

    const paginate = document.getElementById('pagination')
    paginate.addEventListener('change', function(){
        this.form.submit()
    })
</script>
</x-app-layout>
