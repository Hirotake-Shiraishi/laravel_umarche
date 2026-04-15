{{--
    注文詳細ビュー
    $order … 1件の Order。$order->orderItems で明細、$line->product で商品名取得。
    小計 = 明細の price（スナップショット単価） × quantity
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            注文詳細（注文番号: {{ $order->id }}）
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 space-y-4">

                    <div class="text-sm text-gray-600">
                        注文日時: {{ $order->created_at->format('Y/m/d H:i') }}
                    </div>
                    <div>
                        ステータス:
                        @if ($order->status === \Constant::ORDER_STATUS_PENDING)
                            未発送
                        @elseif ($order->status === \Constant::ORDER_STATUS_SHIPPED)
                            発送済み
                        @else
                            {{ $order->status }}
                        @endif
                    </div>

                    <table class="table-auto w-full text-left whitespace-nowrap">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-sm bg-gray-100">商品名</th>
                                <th class="px-4 py-3 text-sm bg-gray-100">単価</th>
                                <th class="px-4 py-3 text-sm bg-gray-100">数量</th>
                                <th class="px-4 py-3 text-sm bg-gray-100">小計</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderItems as $line)
                                <tr class="border-b border-gray-200">
                                    <td class="px-4 py-3">{{ $line->product->name ?? '（削除された商品）' }}</td>
                                    <td class="px-4 py-3">{{ number_format($line->price) }}円</td>
                                    <td class="px-4 py-3">{{ $line->quantity }}</td>
                                    <td class="px-4 py-3">{{ number_format($line->price * $line->quantity) }}円</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="text-right font-semibold">
                        合計: {{ number_format($order->total_price) }}円
                    </div>

                    <div>
                        <a href="{{ route('user.orders.index') }}" class="text-indigo-500">← 注文一覧へ戻る</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
