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

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 space-y-4 min-w-0">

                    <div class="text-xs sm:text-sm text-gray-600 break-words">
                        注文日時: {{ $order->created_at->format('Y/m/d H:i') }}
                    </div>
                    <div class="text-xs sm:text-sm break-words">
                        ステータス:
                        @if ($order->status === \Constant::ORDER_STATUS_PENDING)
                            未発送
                        @elseif ($order->status === \Constant::ORDER_STATUS_SHIPPED)
                            発送済み
                        @else
                            {{ $order->status }}
                        @endif
                    </div>

                    <div class="sm:mx-0 overflow-x-auto overscroll-x-contain [scrollbar-gutter:stable]">
                        <table class="table-auto w-full min-w-[20rem] text-left text-xs sm:text-sm border-collapse">
                        <thead>
                            <tr>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 bg-gray-100 whitespace-normal sm:whitespace-nowrap min-w-[7rem]">商品名</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 bg-gray-100 whitespace-nowrap">単価</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 bg-gray-100 whitespace-nowrap">数量</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 bg-gray-100 whitespace-nowrap">小計</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderItems as $line)
                                <tr class="border-b border-gray-200">
                                    <td class="px-2 sm:px-4 py-2 sm:py-3 break-words whitespace-normal align-top">{{ $line->product->name ?? '（削除された商品）' }}</td>
                                    <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap align-top">{{ number_format($line->price) }}円</td>
                                    <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap align-top">{{ $line->quantity }}</td>
                                    <td class="px-2 sm:px-4 py-2 sm:py-3 whitespace-nowrap align-top">{{ number_format($line->price * $line->quantity) }}円</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>

                    <div class="text-right font-semibold text-sm sm:text-base break-words">
                        合計: {{ number_format($order->total_price) }}円
                    </div>

                    <div>
                        <a href="{{ route('user.orders.index') }}" class="text-indigo-500 text-sm sm:text-base break-words inline-block">← 注文一覧へ戻る</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
