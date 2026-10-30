@if ($paymentMethod == 'razorpay' && $subscriptionRequestRecord->status == 'initiated')

@if (getAppSettings('enable_razorpay'))
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif

    @push('appScripts')
    <script type="text/javascript">
    $("#lwRazorPayBtn").on('click', function() {
        var createOrderUrl = __Utils.apiURL("<?= route('razorpay.api.create_order') ?>");
        __DataRequest.post(createOrderUrl, {
            package_uid: '{{ $subscriptionRequestRecord->_uid }}'
        }, function(orderResponse) {
            if (!orderResponse || orderResponse.reaction != 1 || !orderResponse.data) {
                showAlert(orderResponse.message || '{{ __tr('Failed to create payment order.') }}');
                return;
            }
            var orderData = orderResponse.data;
            var options = {
                key: orderData.key_id,
                amount: orderData.amount,
                currency: orderData.currency,
                order_id: orderData.order_id,
                name: "{{ $planDetails['title'] }}",
                description: "{{ $planDetails['title'] }}",
                handler: function(response) {
                    var verifyUrl = __Utils.apiURL("<?= route('razorpay.api.verify_payment') ?>");
                    __DataRequest.post(verifyUrl, {
                        package_uid: '{{ $subscriptionRequestRecord->_uid }}',
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    }, function(verifyResponse) {
                        if (verifyResponse && verifyResponse.reaction == 1 && verifyResponse.data && verifyResponse.data.redirectRoute) {
                            window.location = verifyResponse.data.redirectRoute;
                            return;
                        }
                        showAlert(verifyResponse.message || (verifyResponse.data && verifyResponse.data.errorMessage) || '{{ __tr('Payment verification failed.') }}');
                    });
                },
                prefill: {
                    name: '<?= getUserAuthInfo('profile.full_name') ?>',
                    email: '<?= getUserAuthInfo('profile.email') ?>',
                },
                notes: {
                    packageUid: '{{ $subscriptionRequestRecord->_uid }}',
                    userId: '<?= getUserID() ?>',
                },
                theme: {
                    color: "#3399cc"
                },
                modal: {
                    ondismiss: function() {
                        showAlert('{{ __tr('Payment cancelled.') }}');
                    }
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function(response) {
                showAlert(response.error.description || '{{ __tr('Payment failed.') }}');
            });
            rzp1.open();
        });
    });
</script>
    @endpush
@endif
