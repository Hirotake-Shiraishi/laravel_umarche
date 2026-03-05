<p>決済ページへ移動しています...</p>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const publicKey = '{{ $publicKey }}'
    // publicKeyをStripeのAPIキーに設定・初期化
    const stripe = Stripe(publicKey)

    // ページが読み込みが完了したら、stripe.redirectToCheckoutを実行
    window.onload = function() {
        // checkout.stripe.comにリダイレクトして、セッションIDを渡して、決済ページを表示する。
        stripe.redirectToCheckout({
            sessionId: '{{ $session->id }}'

        }).then(function(result) {
            // 決済が失敗した場合は、カートページにリダイレクトする。
            window.location.href = '{{ route('user.cart.index') }}';
        });
    }
</script>
