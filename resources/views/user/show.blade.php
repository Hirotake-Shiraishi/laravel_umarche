<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight break-words">
            商品の詳細
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-5 sm:p-8 bg-white border-b border-gray-200 min-w-0">
                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between lg:gap-10 min-w-0">
                        <div class="w-full lg:w-1/2 shrink-0 min-w-0">
                            {{-- <x-thumbnail filename="{{ $product->imageFirst->filename ?? '' }}" type="products" /> --}}
                            <!-- Slider main container -->
                            <div class="swiper-container w-full max-w-full min-w-0">
                                <!-- Additional required wrapper -->
                                <div class="swiper-wrapper">
                                    <!-- Slides -->
                                    <div class="swiper-slide">
                                        @if ($product->imageFirst->filename !== null)
                                            <img class="w-full max-h-80 sm:max-h-96 object-contain mx-auto bg-gray-50"
                                                src="{{ asset('storage/products/' . $product->imageFirst->filename) }}"
                                                alt="">
                                        @else
                                            <div class="w-full h-48 sm:h-64 max-w-md bg-gray-100 rounded-md mx-auto" aria-hidden="true"></div>
                                        @endif
                                    </div>
                                    <div class="swiper-slide">
                                        @if ($product->imageSecond->filename !== null)
                                            <img class="w-full max-h-80 sm:max-h-96 object-contain mx-auto bg-gray-50"
                                                src="{{ asset('storage/products/' . $product->imageSecond->filename) }}"
                                                alt="">
                                        @else
                                            <div class="w-full h-48 sm:h-64 max-w-md bg-gray-100 rounded-md mx-auto" aria-hidden="true"></div>
                                        @endif
                                    </div>
                                    <div class="swiper-slide">
                                        @if ($product->imageThird->filename !== null)
                                            <img class="w-full max-h-80 sm:max-h-96 object-contain mx-auto bg-gray-50"
                                                src="{{ asset('storage/products/' . $product->imageThird->filename) }}"
                                                alt="">
                                        @else
                                            <div class="w-full h-48 sm:h-64 max-w-md bg-gray-100 rounded-md mx-auto" aria-hidden="true"></div>
                                        @endif
                                    </div>
                                    <div class="swiper-slide">
                                        @if ($product->imageFourth->filename !== null)
                                            <img class="w-full max-h-80 sm:max-h-96 object-contain mx-auto bg-gray-50"
                                                src="{{ asset('storage/products/' . $product->imageFourth->filename) }}"
                                                alt="">
                                        @else
                                            <div class="w-full h-48 sm:h-64 max-w-md bg-gray-100 rounded-md mx-auto" aria-hidden="true"></div>
                                        @endif
                                    </div>
                                </div>
                                <!-- If we need pagination -->
                                <div class="swiper-pagination"></div>

                                <!-- If we need navigation buttons -->
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>

                                <!-- If we need scrollbar -->
                                <div class="swiper-scrollbar"></div>
                            </div>
                        </div>
                        <div class="w-full lg:w-1/2 min-w-0 lg:max-w-xl">
                            <h2 class="mb-2 sm:mb-4 text-xs sm:text-sm title-font text-gray-500 tracking-widest break-words">
                                {{ $product->secondaryCategory->name }}</h2>
                            <h1 class="mb-3 sm:mb-4 text-gray-900 text-xl sm:text-2xl md:text-3xl title-font font-medium break-words">{{ $product->name }}</h1>
                            <p class="mb-4 sm:mb-6 leading-relaxed text-sm sm:text-base text-gray-700 break-words">{{ $product->information }}</p>
                            <div class="flex flex-col gap-5 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between sm:gap-6">
                                <div class="shrink-0">
                                    <span
                                        class="title-font font-medium text-xl sm:text-2xl text-gray-900">{{ number_format($product->price) }}</span>
                                    <span class="text-xs sm:text-sm text-gray-700">円(税込)</span>
                                </div>
                                {{-- カート追加フォーム --}}
                                <form method="post" action="{{ route('user.cart.add') }}" class="w-full min-w-0 sm:w-auto sm:max-w-xs">
                                    @csrf
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4">
                                        <span class="text-sm sm:text-base text-gray-700 shrink-0">数量</span>
                                        <div class="relative min-w-0 flex-1 sm:flex-initial">
                                            <select name="quantity"
                                                class="w-full sm:w-auto min-w-0 rounded-md border appearance-none border-gray-300 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-base pl-3 pr-10 bg-white">
                                                @for($i = 1; $i <= $quantity; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="w-full sm:w-auto block sm:inline-flex justify-center text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded-md text-sm sm:text-base">カートに入れる</button>
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-300 my-8 sm:my-10"></div>

                    {{-- レビュー平均評価表示 --}}
                    <div class="space-y-5 sm:space-y-6">
                        <h3 class="text-lg font-semibold text-gray-800">レビュー</h3>

                        {{-- 平均評価（レビュー0件のときは null） --}}
                        @php
                            // withAvg('reviews','rating') で付与される属性（Laravel の規則）
                            $avg = $product->reviews_avg_rating;

                            // 表示用: 小数1桁（例: 4.2）
                            $avgText = is_null($avg) ? null : number_format((float) $avg, 1);

                            // 星表示は「四捨五入して整数にする」ルールで実装する
                            $avgRounded = is_null($avg) ? 0 : (int) round((float) $avg);
                        @endphp

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2.5 min-w-0">
                            <div class="text-yellow-500 text-lg shrink-0">
                                @for ($i = 1; $i <= 5; $i++)
                                    {{ $i <= $avgRounded ? '★' : '☆' }}
                                @endfor
                            </div>
                            <div class="text-sm text-gray-600 min-w-0 break-words">
                                @if (is_null($avgText))
                                    {{-- まだレビューがありません。 --}}
                                @else
                                    平均 {{ $avgText }} / 5（{{ $product->reviews->count() }}件）
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- レビュー一覧 --}}
                    <div class="mt-8 sm:mt-10 space-y-4 sm:space-y-5">
                        @forelse ($product->reviews as $review)
                            <div class="border rounded-lg p-4 sm:p-5 min-w-0 overflow-hidden">
                                <div
                                    class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-4 min-w-0">
                                    <div class="text-sm text-gray-700 font-semibold min-w-0 break-words">
                                        {{ $review->user->name ?? '（退会ユーザー）' }}
                                    </div>
                                    <div class="text-xs text-gray-500 shrink-0 whitespace-nowrap sm:text-right">
                                        {{ optional($review->created_at)->format('Y/m/d') }}
                                    </div>
                                </div>

                                <div class="mt-2.5 text-yellow-500 shrink-0">
                                    @for ($i = 1; $i <= 5; $i++)
                                        {{ $i <= (int) $review->rating ? '★' : '☆' }}
                                    @endfor
                                </div>

                                @if (!is_null($review->comment) && $review->comment !== '')
                                    <div class="mt-3 text-sm text-gray-700 whitespace-pre-wrap break-words leading-relaxed">
                                        {{ $review->comment }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-sm text-gray-600 py-2">
                                まだレビューがありません。
                            </div>
                        @endforelse
                    </div>

                    {{-- レビュー投稿フォーム（購入済み & 未投稿のときだけ表示） --}}
                    @if ($canReview)
                        <div class="mt-8 sm:mt-10 border rounded-lg p-4 sm:p-6 bg-gray-50 min-w-0">
                            <h4 class="font-semibold text-gray-800 mb-4 sm:mb-5">レビューを投稿する（購入者限定）</h4>

                            {{-- ReviewController::store で withErrors(['review' => ...]) を返す場合がある --}}
                            @if ($errors->has('review'))
                                <div class="mb-3 text-sm text-red-600">
                                    {{ $errors->first('review') }}
                                </div>
                            @endif

                            <form method="post" action="{{ route('user.items.reviews.store', ['item' => $product->id]) }}">
                                @csrf

                                <div class="mb-4">
                                    <label class="block text-sm text-gray-700 mb-2">評価（1〜5）</label>
                                    <select name="rating"
                                        class="w-full rounded border border-gray-300 py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" @if ((int) old('rating', 5) === $i) selected @endif>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('rating')
                                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-5">
                                    <label class="block text-sm text-gray-700 mb-2">コメント（任意）</label>
                                    <textarea name="comment" rows="4"
                                        class="w-full rounded border border-gray-300 py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
                                        placeholder="例）梱包が丁寧でした。味も満足です。">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button
                                    class="mt-1 text-white bg-indigo-500 border-0 py-2.5 px-8 focus:outline-none hover:bg-indigo-600 rounded-md">
                                    投稿する
                                </button>
                            </form>
                        </div>
                    @elseif ($hasPurchased && $userReview)
                        {{-- 購入済みだが投稿済みの場合 --}}
                        <div class="mt-8 sm:mt-10 text-sm text-gray-600 leading-relaxed">
                            この商品へのレビューは投稿済みです（1回のみ投稿可能）。
                        </div>
                    @elseif (!$hasPurchased)
                        {{-- 未購入の場合 --}}
                        <div class="mt-8 sm:mt-10 text-sm text-gray-600 leading-relaxed">
                            レビューの投稿は購入済みの方のみ可能です。
                        </div>
                    @endif

                    {{-- ショップ情報 --}}
                    <div class="mt-10 sm:mt-12 pt-8 sm:pt-10 border-t border-gray-200">
                    <div class="mb-3 text-center text-sm sm:text-base text-gray-600">この商品を販売しているショップ</div>
                    <div class="mb-4 text-center text-base sm:text-lg font-medium text-gray-900 break-words px-2">{{ $product->shop->name }}</div>
                    <div class="mb-6 flex justify-center">
                        @if ($product->shop->filename !== null)
                            <img class="w-32 h-32 sm:w-40 sm:h-40 rounded-full object-cover border border-gray-100"
                                src="{{ asset('storage/shops/' . $product->shop->filename) }}" alt="">
                        @else
                            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-gray-100 border border-gray-200 mx-auto" aria-hidden="true"></div>
                        @endif
                    </div>
                    <div class="flex justify-center px-2 pb-1">
                        <button type="button" data-micromodal-trigger="modal-1" href='javascript:;'
                            class="w-full max-w-sm sm:w-auto text-white bg-gray-400 border-0 py-2.5 px-8 focus:outline-none hover:bg-gray-500 rounded-md text-sm sm:text-base">ショップの詳細をみる</button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal micromodal-slide" id="modal-1" aria-hidden="true">
        <div class="modal__overlay z-10" tabindex="-1" data-micromodal-close>
            <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                <header class="modal__header">
                    <h2 class="text-xl text-gray-700" id="modal-1-title">
                        {{ $product->shop->name }}
                    </h2>
                    <button type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                </header>
                <main class="modal__content" id="modal-1-content">
                    <p class="break-words">
                        {{ $product->shop->information }}
                    </p>
                </main>
                <footer class="modal__footer">
                    <button type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">閉じる</button>
                </footer>
            </div>
        </div>
    </div>
    <script src="{{ mix('js/swiper.js') }}"></script>
</x-app-layout>
