{{--
    オーナー向け「レビュー一覧」ビュー
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            レビュー一覧
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">
                    <x-flash-message status="session('status')" />

                    @if ($reviews->isEmpty())
                        <p class="text-sm text-gray-600 py-1">レビューはまだありません。</p>
                    @else
                        <div
                            class="w-full min-w-0 -mx-3 px-3 sm:mx-0 sm:px-0 overflow-x-auto overscroll-x-contain touch-pan-x [scrollbar-gutter:stable] [-webkit-overflow-scrolling:touch]">
                            <table class="table-auto w-full min-w-[20rem] sm:min-w-[40rem] text-left text-xs sm:text-sm border-collapse">
                                <thead>
                                    <tr>
                                        <th class="px-3 sm:px-5 py-3 sm:py-3.5 bg-gray-100 whitespace-nowrap">日時</th>
                                        <th
                                            class="px-3 sm:px-5 py-3 sm:py-3.5 bg-gray-100 whitespace-normal sm:whitespace-nowrap max-w-[9rem] sm:max-w-none">商品</th>
                                        <th class="px-3 sm:px-5 py-3 sm:py-3.5 bg-gray-100 whitespace-nowrap">評価</th>
                                        <th class="px-3 sm:px-5 py-3 sm:py-3.5 bg-gray-100 whitespace-nowrap">投稿者</th>
                                        <th class="px-3 sm:px-5 py-3 sm:py-3.5 bg-gray-100">コメント</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reviews as $review)
                                        <tr class="border-b border-gray-200 align-top">
                                            <td class="px-3 sm:px-5 py-3 sm:py-3.5 text-gray-600 whitespace-nowrap align-top">
                                                {{ optional($review->created_at)->format('Y/m/d H:i') }}
                                            </td>
                                            <td
                                                class="px-3 sm:px-5 py-3 sm:py-3.5 break-words whitespace-normal sm:whitespace-nowrap max-w-[9rem] sm:max-w-none align-top">
                                                {{ $review->product->name ?? '（削除された商品）' }}
                                            </td>
                                            <td class="px-3 sm:px-5 py-3 sm:py-3.5 text-yellow-500 whitespace-nowrap align-top">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    {{ $i <= (int) $review->rating ? '★' : '☆' }}
                                                @endfor
                                            </td>
                                            <td class="px-3 sm:px-5 py-3 sm:py-3.5 whitespace-nowrap align-top">
                                                {{ $review->user->name ?? '（退会ユーザー）' }}
                                            </td>
                                            <td
                                                class="px-3 sm:px-5 py-3 sm:py-3.5 text-gray-700 whitespace-pre-wrap break-words min-w-[12rem] sm:min-w-[14rem] align-top leading-relaxed">
                                                {{ $review->comment }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div
                            class="mt-6 sm:mt-8 min-w-0 overflow-x-auto overscroll-x-contain touch-pan-x [-webkit-overflow-scrolling:touch]">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

