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
                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.shops.update', ['shop' => $shop->id]) }}"
                        enctype="multipart/form-data"
                        class="w-full max-w-lg md:max-w-xl mx-auto min-w-0">
                        @csrf
                        <div class="space-y-4 sm:space-y-5">
                            {{-- 店名 --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="name"
                                        class="leading-7 text-sm text-gray-600">店名 ※必須</label>
                                    <input type="text" id="name" name="name" required
                                        value="{{ $shop->name }}"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                </div>
                            </div>
                            {{-- 店舗情報 --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="information"
                                        class="leading-7 text-sm text-gray-600">店舗情報 ※必須</label>
                                    <textarea id="information" name="information" required rows="10"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-relaxed transition-colors duration-200 ease-in-out resize-y">{{ $shop->information }}</textarea>
                                </div>
                            </div>
                            {{-- 画像サムネイル --}}
                            <div class="w-full">
                                <div class="relative">
                                    <div class="w-full max-w-xs sm:max-w-sm">
                                        <x-thumbnail :filename="$shop->filename" type="shops" />
                                    </div>
                                </div>
                            </div>
                            {{-- 画像 --}}
                            <div class="w-full">
                                <div class="relative">
                                    <label for="image" class="leading-7 text-sm text-gray-600">画像</label>
                                    <input type="file" id="image" name="image"
                                        accept="image/png, image/jpeg, image/jpg"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-sm sm:text-base outline-none text-gray-700 py-2 px-3 transition-colors duration-200 ease-in-out">
                                </div>
                            </div>
                            {{-- 販売中/停止中 --}}
                            <div class="w-full">
                                <div class="relative flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:justify-center sm:items-center sm:gap-6 text-sm sm:text-base">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="is_selling" value="1" class="mr-2" @if($shop->is_selling === 1) checked @endif>
                                        <span>販売中</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="is_selling" value="0" class="mr-2" @if($shop->is_selling === 0) checked @endif>
                                        <span>停止中</span>
                                    </label>
                                </div>
                            </div>
                            {{-- ボタン --}}
                            <div class="flex flex-col-reverse sm:flex-row sm:justify-center gap-3 sm:gap-6 pt-2">
                                <button type="button" onclick="location.href='{{ route('owner.shops.index') }}'"
                                    class="w-full sm:w-auto bg-gray-200 border-0 py-2 px-6 sm:px-8 focus:outline-none hover:bg-gray-400 rounded text-sm sm:text-lg">戻る</button>
                                <button type="submit"
                                    class="w-full sm:w-auto text-white bg-indigo-500 border-0 py-2 px-6 sm:px-8 focus:outline-none hover:bg-indigo-600 rounded text-sm sm:text-lg">更新する</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
