{{--
    注文一覧ビュー
    $orders … コントローラから渡された LengthAwarePaginator（ページネーション付きコレクション）
    @foreach で1件ずつ表示し、{{ $orders->links() }} でページ送りリンクを出す。
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            注文履歴
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 min-w-0">

                    @if ($orders->isEmpty())
                        <p class="text-sm text-gray-600">まだ注文がありません。</p>
                    @else
                        <div class="-mx-4 sm:mx-0 overflow-x-auto overscroll-x-contain">
                            <table class="table-auto w-full min-w-max text-left text-xs sm:text-sm border-collapse">
                            <thead>
                                <tr>
                                    <th
                                        class="px-2 sm:px-4 py-2 sm:py-3 title-font tracking-wider font-medium text-gray-900 bg-gray-100 rounded-tl whitespace-nowrap">
                                        注文日時</th>
                                    <th
                                        class="px-2 sm:px-4 py-2 sm:py-3 title-font tracking-wider font-medium text-gray-900 bg-gray-100 whitespace-nowrap">
                                        合計</th>
                                    <th
                                        class="px-2 sm:px-4 py-2 sm:py-3 title-font tracking-wider font-medium text-gray-900 bg-gray-100 whitespace-nowrap">
                                        ステータス</th>
                                    <th
                                        class="px-2 sm:px-4 py-2 sm:py-3 title-font tracking-wider font-medium text-gray-900 bg-gray-100 whitespace-nowrap rounded-tr">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr class="border-b border-gray-200">
                                        <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap">{{ $order->created_at->format('Y/m/d H:i') }}</td>
                                        <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap">{{ number_format($order->total_price) }}円</td>
                                        <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                            {{-- DB には英語（pending / shipped）、画面では日本語で表示 --}}
                                            @if ($order->status === \Constant::ORDER_STATUS_PENDING)
                                                未発送
                                            @elseif ($order->status === \Constant::ORDER_STATUS_SHIPPED)
                                                発送済み
                                            @else
                                                {{ $order->status }}
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                            {{-- route() … 名前付きルートから URL を生成。第2引数でルートパラメータ {order} に id を渡す --}}
                                            <a href="{{ route('user.orders.show', ['order' => $order->id]) }}"
                                                class="text-indigo-500 text-sm sm:text-base hover:underline">詳細</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            {{ $orders->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
