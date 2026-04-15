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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if ($orders->isEmpty())
                        <p>まだ注文がありません。</p>
                    @else
                        <table class="table-auto w-full text-left whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th
                                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                                        注文日時</th>
                                    <th
                                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                        合計</th>
                                    <th
                                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                        ステータス</th>
                                    <th
                                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tr rounded-br">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr class="border-b border-gray-200">
                                        <td class="px-4 py-3">{{ $order->created_at->format('Y/m/d H:i') }}</td>
                                        <td class="px-4 py-3">{{ number_format($order->total_price) }}円</td>
                                        <td class="px-4 py-3">
                                            {{-- DB には英語（pending / shipped）、画面では日本語で表示 --}}
                                            @if ($order->status === \Constant::ORDER_STATUS_PENDING)
                                                未発送
                                            @elseif ($order->status === \Constant::ORDER_STATUS_SHIPPED)
                                                発送済み
                                            @else
                                                {{ $order->status }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            {{-- route() … 名前付きルートから URL を生成。第2引数でルートパラメータ {order} に id を渡す --}}
                                            <a href="{{ route('user.orders.show', ['order' => $order->id]) }}"
                                                class="text-indigo-500">詳細</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $orders->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
