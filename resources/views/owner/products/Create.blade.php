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
                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.products.store') }}" class="w-full max-w-lg md:max-w-xl mx-auto min-w-0">
                        @csrf
                        <div class="space-y-4 sm:space-y-5">
                            {{-- 商品名 --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="name"
                                        class="leading-7 text-sm text-gray-600">商品名 ※必須</label>
                                    <input type="text" id="name" name="name" required
                                        value="{{ old('name') }}"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded-md border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                </div>
                            </div>
                            {{-- 商品情報 --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="information"
                                        class="leading-7 text-sm text-gray-600">商品情報 ※必須</label>
                                    <textarea id="information" name="information" required rows="10"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded-md border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-relaxed transition-colors duration-200 ease-in-out resize-y">{{ old('information') }}</textarea>
                                </div>
                            </div>
                            {{-- 価格 --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="price"
                                        class="leading-7 text-sm text-gray-600">価格 ※必須</label>
                                    <input type="number" id="price" name="price" required
                                        value="{{ old('price') }}"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded-md border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                </div>
                            </div>
                            {{-- 表示順 --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="sort_order"
                                        class="leading-7 text-sm text-gray-600">表示順</label>
                                    <input type="number" id="sort_order" name="sort_order"
                                        value="{{ old('sort_order') }}"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded-md border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                </div>
                            </div>
                            {{-- 初期在庫数 --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="quantity"
                                        class="leading-7 text-sm text-gray-600">初期在庫数 ※必須</label>
                                    <input type="number" id="quantity" name="quantity" required
                                        value="{{ old('quantity') }}"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded-md border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                    <span class="block mt-1 text-xs sm:text-sm text-gray-600 break-words">0～99の範囲で入力してください</span>
                                </div>
                            </div>
                            {{-- 販売する店舗--}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="shop_id" class="leading-7 text-sm text-gray-600">販売する店舗</label>
                                    <select name="shop_id" id="shop_id" class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded-md border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 transition-colors duration-200 ease-in-out">
                                        @foreach ($shops as $shop )
                                            <option value="{{ $shop->id }}">
                                                {{ $shop->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- カテゴリー　プルダウン --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="category" class="leading-7 text-sm text-gray-600">カテゴリー</label>
                                    <select name="category" id="category" class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded-md border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 transition-colors duration-200 ease-in-out">
                                        @foreach ($categories as $category)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach ($category->secondary as $secondary)
                                                    <option value="{{ $secondary->id }}">
                                                        {{ $secondary->name }}
                                                    </option>
                                                @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- 画像の選択 --}}
                            <div class="w-full min-w-0 space-y-4 sm:space-y-5">
                                <x-select-image :images="$images" name="image1" />
                                <x-select-image :images="$images" name="image2" />
                                <x-select-image :images="$images" name="image3" />
                                <x-select-image :images="$images" name="image4" />
                            </div>
                            {{-- 販売中/停止中 --}}
                            <div class="w-full">
                                <div class="relative flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:justify-center sm:items-center sm:gap-6 text-sm sm:text-base">
                                    <div class="inline-flex items-center"><input type="radio" name="is_selling" value="1" class="mr-2" checked>販売中</div>
                                    <div class="inline-flex items-center"><input type="radio" name="is_selling" value="0" class="mr-2">停止中</div>
                                </div>
                            </div>
                            {{-- ボタン --}}
                            <div class="flex flex-col-reverse sm:flex-row sm:justify-center gap-3 sm:gap-6 pt-2">
                                <button type="button" onclick="location.href='{{ route('owner.products.index') }}'"
                                    class="w-full sm:w-auto bg-gray-200 border-0 py-2 px-6 sm:px-8 focus:outline-none hover:bg-gray-400 rounded-md text-sm sm:text-lg">戻る</button>
                                <button type="submit"
                                    class="w-full sm:w-auto text-white bg-indigo-500 border-0 py-2 px-6 sm:px-8 focus:outline-none hover:bg-indigo-600 rounded-md text-sm sm:text-lg">登録する</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        'use strict'
        const images = document.querySelectorAll('.image');

        images.forEach(image => {
            image.addEventListener('click', function(e) {
                const imageName = e.target.dataset.id.substr(0, 6);
                const imageId = e.target.dataset.id.replace(imageName + '_', '');
                const imageFile = e.target.dataset.file;
                const imagePath = e.target.dataset.path;
                const modal = e.target.dataset.modal;

                document.getElementById(imageName + '_thumbnail').src = imagePath + '/' + imageFile;
                document.getElementById(imageName + '_hidden').value = imageId;
                // MicroModal.close(modal);
            })
        })
    </script>
</x-app-layout>
