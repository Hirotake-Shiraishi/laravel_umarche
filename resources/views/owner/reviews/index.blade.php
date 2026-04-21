{{--
    オーナー向け「レビュー一覧」ビュー
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            レビュー一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-flash-message status="session('status')" />

                    @if ($reviews->isEmpty())
                        <p>レビューはまだありません。</p>
                    @else
                        <table class="table-auto w-full text-left whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-sm bg-gray-100">日時</th>
                                    <th class="px-4 py-3 text-sm bg-gray-100">商品</th>
                                    <th class="px-4 py-3 text-sm bg-gray-100">評価</th>
                                    <th class="px-4 py-3 text-sm bg-gray-100">投稿者</th>
                                    <th class="px-4 py-3 text-sm bg-gray-100">コメント</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reviews as $review)
                                    <tr class="border-b border-gray-200 align-top">
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ optional($review->created_at)->format('Y/m/d H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $review->product->name ?? '（削除された商品）' }}
                                        </td>
                                        <td class="px-4 py-3 text-yellow-500">
                                            @for ($i = 1; $i <= 5; $i++)
                                                {{ $i <= (int) $review->rating ? '★' : '☆' }}
                                            @endfor
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $review->user->name ?? '（退会ユーザー）' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-pre-wrap">
                                            {{ $review->comment }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

