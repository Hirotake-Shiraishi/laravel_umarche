{{--
    オーナー向け受注一覧
    $shopId … 自店の ID。明細の product.shop_id がすべてこれと一致するときだけステータス更新フォーム<form>を表示する。
    @method('PATCH') … HTML フォームは GET/POST しか送れないため、Laravel が PATCH として解釈する隠しフィールド。
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            受注一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if (session('message'))
                        <div class="mb-4 p-2 bg-blue-100 text-blue-800 rounded">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if ($orders->isEmpty())
                        <p>表示する受注はありません。</p>
                    @else
                        <table class="table-auto w-full text-left text-sm whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th class="px-2 py-2 bg-gray-100">注文ID</th>
                                    <th class="px-2 py-2 bg-gray-100">購入者</th>
                                    <th class="px-2 py-2 bg-gray-100">日時</th>
                                    <th class="px-2 py-2 bg-gray-100">合計</th>
                                    <th class="px-2 py-2 bg-gray-100">ステータス</th>
                                    <th class="px-2 py-2 bg-gray-100">更新</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    @php
                                        /*
                                         * 明細に含まれるすべて商品が、自店（$shopId）のものかどうか。
                                         * - false のときは他店舗と混在した注文なので、
                                         *   ステータス変更で他店分まで済ませてしまわないようフォーム<form>を表示しない。
                                         */
                                        $canUpdateStatus = $order->orderItems->every(function ($line) use ($shopId) {
                                            return $line->product && (int) $line->product->shop_id === (int) $shopId;
                                        });
                                    @endphp
                                    <tr class="border-b border-gray-200">
                                        <td class="px-2 py-2">{{ $order->id }}</td>
                                        <td class="px-2 py-2">{{ $order->user->name ?? '-' }}</td>
                                        <td class="px-2 py-2">{{ $order->created_at->format('Y/m/d H:i') }}</td>
                                        <td class="px-2 py-2">{{ number_format($order->total_price) }}円</td>
                                        <td class="px-2 py-2">
                                            @if ($order->status === \Constant::ORDER_STATUS_PENDING)
                                                未発送
                                            @elseif ($order->status === \Constant::ORDER_STATUS_SHIPPED)
                                                発送済み
                                            @else
                                                {{ $order->status }}
                                            @endif
                                        </td>
                                        <td class="px-2 py-2">
                                            @if ($canUpdateStatus)
                                                <form method="post"
                                                    action="{{ route('owner.orders.updateStatus', ['order' => $order->id]) }}"
                                                    class="flex items-center space-x-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status"
                                                        class="border border-gray-300 rounded text-sm">
                                                        <option value="{{ \Constant::ORDER_STATUS_PENDING }}"
                                                            @if ($order->status === \Constant::ORDER_STATUS_PENDING) selected @endif>未発送
                                                        </option>
                                                        <option value="{{ \Constant::ORDER_STATUS_SHIPPED }}"
                                                            @if ($order->status === \Constant::ORDER_STATUS_SHIPPED) selected @endif>発送済み
                                                        </option>
                                                    </select>
                                                    <button type="submit"
                                                        class="text-white bg-indigo-500 border-0 py-1 px-3 rounded text-sm">保存</button>
                                                </form>
                                            @else
                                                <span class="text-gray-500 text-xs">他店舗商品を含むため更新不可</span>
                                            @endif
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
