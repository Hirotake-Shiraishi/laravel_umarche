<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight break-words">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">
                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.images.update', ['image' => $image->id]) }}"
                        class="w-full max-w-lg md:max-w-xl mx-auto min-w-0">
                        @csrf
                        @method('put')
                        <div class="space-y-4 sm:space-y-5">
                            {{-- 画像タイトル --}}
                            <div class="w-full min-w-0">
                                <div class="relative">
                                    <label for="title" class="leading-7 text-sm text-gray-600">画像タイトル</label>
                                    <input type="text" id="title" name="title" value="{{ $image->title }}"
                                        class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded-md border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                </div>
                            </div>
                            {{-- 画像サムネイル --}}
                            <div class="w-full flex justify-center">
                                <div class="relative w-60 sm:w-80 shrink-0">
                                    <x-thumbnail :filename="$image->filename" type="products" />
                                </div>
                            </div>
                            {{-- 更新ボタン/戻るボタン --}}
                            <div class="flex flex-col-reverse sm:flex-row sm:justify-center gap-3 sm:gap-6 pt-2">
                                <button type="button" onclick="location.href='{{ route('owner.images.index') }}'"
                                    class="w-full sm:w-auto bg-gray-200 border-0 py-2 px-6 sm:px-8 focus:outline-none hover:bg-gray-400 rounded-md text-sm sm:text-lg">戻る</button>
                                <button type="submit"
                                    class="w-full sm:w-auto text-white bg-indigo-500 border-0 py-2 px-6 sm:px-8 focus:outline-none hover:bg-indigo-600 rounded-md text-sm sm:text-lg">更新する</button>
                            </div>
                        </div>
                    </form>
                    {{-- 削除ボタン --}}
                    <form id="delete_{{ $image->id }}" method="post"
                        action="{{ route('owner.images.destroy', ['image' => $image->id]) }}"
                        class="w-full max-w-lg md:max-w-xl mx-auto min-w-0 mt-8 sm:mt-10">
                        @csrf
                        @method('delete')
                        <div class="flex justify-center sm:justify-start pt-2 border-t border-gray-200">
                            <a href="#" data-id="{{ $image->id }}"
                                onclick="deletePost(this)"
                                class="w-full sm:w-auto text-center text-white bg-red-400 border-0 py-2 px-6 sm:px-4 focus:outline-none hover:bg-red-500 rounded-md text-sm sm:text-base">削除する</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function deletePost(e) {
            'use strict';
            if (confirm('本当に削除してもいいですか?')) {
                document.getElementById('delete_' + e.dataset.id).submit();
            }
        }
    </script>
</x-app-layout>
