<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            期限切れオーナ一
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">
                    <section class="text-gray-600 body-font min-w-0">
                        <div class="container max-w-full px-0 sm:px-4 mx-auto min-w-0">
                            {{-- トースト・フラッシュメッセージの表示 --}}
                            <x-flash-message status="session('status')" />
                            <div class="w-full max-w-4xl mx-auto min-w-0">
                                <div class="-mx-4 sm:mx-0 overflow-x-auto overscroll-x-contain [scrollbar-gutter:stable]">
                                    <table class="table-auto w-full min-w-[36rem] text-left text-xs sm:text-sm border-collapse">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-2 sm:px-4 py-2 sm:py-3 title-font tracking-wider font-medium text-gray-900 bg-gray-100 rounded-tl whitespace-nowrap">
                                                名前</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 sm:py-3 title-font tracking-wider font-medium text-gray-900 bg-gray-100 whitespace-nowrap">
                                                メールアドレス</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 sm:py-3 title-font tracking-wider font-medium text-gray-900 bg-gray-100 whitespace-nowrap">
                                                期限が切れた日</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 sm:py-3 title-font tracking-wider font-medium text-gray-900 bg-gray-100 whitespace-nowrap rounded-tr">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($expiredOwners as $owner)
                                            <tr class="border-b border-gray-200">
                                                <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap">{{ $owner->name }}</td>
                                                <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap">{{ $owner->email }}</td>
                                                <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap">{{ $owner->deleted_at->diffForHumans() }}</td>

                                                <form id="delete_{{$owner->id}}" method="post"
                                                    action="{{ route('admin.expired-owners.destroy', ['owner' => $owner->id]) }}">
                                                    @csrf
                                                    <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap align-top">
                                                        <a href="#" data-id="{{ $owner->id }}" onclick="deletePost(this)"
                                                            class="inline-block text-white bg-red-400 border-0 py-1.5 px-2 sm:py-2 sm:px-4 text-xs sm:text-sm focus:outline-none hover:bg-red-500 rounded whitespace-nowrap">完全削除</a>
                                                    </td>
                                                </form>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
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
