<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            オーナー情報 編集
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">
                    <section class="text-gray-600 body-font relative min-w-0">
                        <div class="container max-w-full px-0 sm:px-4 mx-auto min-w-0">
                            <div class="flex flex-col text-center w-full mb-8 sm:mb-12">
                                <h1 class="text-xl sm:text-2xl md:text-3xl font-medium title-font mb-4 text-gray-900 break-words px-1">オーナー情報 編集</h1>
                            </div>
                            <div class="w-full max-w-lg md:max-w-xl mx-auto min-w-0">
                                <!-- Validation Errors -->
                                <x-auth-validation-errors class="mb-4" :errors="$errors" />
                                <form action="{{ route('admin.owners.update', ['owner' => $owner->id]) }}" method="post">
                                    @method('PUT')
                                    @csrf
                                    <div class="space-y-4 sm:space-y-5">
                                        <div class="w-full">
                                            <div class="relative">
                                                <label for="name"
                                                    class="leading-7 text-sm text-gray-600">オーナー名</label>
                                                <input type="text" id="name" name="name" required
                                                    value="{{ $owner->name }}"
                                                    class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            </div>
                                        </div>
                                        <div class="w-full">
                                            <div class="relative">
                                                <label for="email"
                                                    class="leading-7 text-sm text-gray-600">メールアドレス</label>
                                                <input type="email" id="email" name="email"
                                                    value="{{ $owner->email }}"
                                                    class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            </div>
                                        </div>
                                        <div class="w-full">
                                            <div class="relative">
                                                <label for="shop" class="leading-7 text-sm text-gray-600">店名</label>
                                                <div class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded border border-transparent text-base text-gray-700 py-2 px-3 leading-8 break-words">
                                                    {{ $owner->shop->name }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="w-full">
                                            <div class="relative">
                                                <label for="password"
                                                    class="leading-7 text-sm text-gray-600">パスワード</label>
                                                <input type="password" id="password" name="password"
                                                    class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            </div>
                                        </div>
                                        <div class="w-full">
                                            <div class="relative">
                                                <label for="password_confirmation"
                                                    class="leading-7 text-sm text-gray-600">パスワード（確認）</label>
                                                <input type="password" id="password_confirmation"
                                                    name="password_confirmation"
                                                    class="w-full min-w-0 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            </div>
                                        </div>
                                        <div class="flex flex-col-reverse sm:flex-row sm:justify-center gap-3 sm:gap-6 pt-2">
                                            <button type="button"
                                                onclick="location.href='{{ route('admin.owners.index') }}'"
                                                class="w-full sm:w-auto bg-gray-200 border-0 py-2.5 px-6 sm:px-8 focus:outline-none hover:bg-gray-400 rounded text-sm sm:text-lg">戻る</button>
                                            <button type="submit"
                                                class="w-full sm:w-auto text-white bg-indigo-500 border-0 py-2.5 px-6 sm:px-8 focus:outline-none hover:bg-indigo-600 rounded text-sm sm:text-lg">更新する</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
