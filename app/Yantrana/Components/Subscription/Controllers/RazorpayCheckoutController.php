<?php
/**
 * WhatsJet - Razorpay Standard Checkout (create order + verify signature)
 */

namespace App\Yantrana\Components\Subscription\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Components\Subscription\ManualSubscriptionEngine;
use App\Yantrana\Components\Subscription\PaymentEngines\RazorpayEngine;
use App\Yantrana\Components\Subscription\Repositories\ManualSubscriptionRepository;

class RazorpayCheckoutController extends BaseController
{
    public function __construct(
        protected RazorpayEngine $razorpayEngine,
        protected ManualSubscriptionRepository $manualSubscriptionRepository,
        protected ManualSubscriptionEngine $manualSubscriptionEngine,
    ) {
    }

    /**
     * POST /api/create-order — create Razorpay order (amount from subscription, not client).
     */
    public function createOrder(BaseRequest $request)
    {
        validateVendorAccess('administrative');

        if (! getAppSettings('enable_razorpay')) {
            return $this->processResponse([
                'reaction_code' => 2,
                'message' => __tr('Razorpay is not enabled.'),
            ], [], [], true);
        }

        $request->validate([
            'package_uid' => 'required|uuid',
        ]);

        $subscriptionRequest = $this->manualSubscriptionRepository->fetchIt([
            '_uid' => $request->package_uid,
            'vendors__id' => getVendorId(),
            'status' => 'initiated',
        ]);

        if (__isEmpty($subscriptionRequest)) {
            return $this->processResponse([
                'reaction_code' => 18,
                'message' => __tr('Invalid subscription request.'),
            ], [], [], true);
        }

        $amountPaise = (int) round((float) $subscriptionRequest->charges * 100);
        if ($amountPaise < 100) {
            return $this->processResponse([
                'reaction_code' => 2,
                'message' => __tr('Minimum amount is 100 paise.'),
            ], [], [], true);
        }

        $currency = strtoupper(getAppSettings('currency') ?: 'INR');
        $orderReaction = $this->razorpayEngine->createOrder(
            $amountPaise,
            $currency,
            'sub_' . $subscriptionRequest->_uid,
            [
                'packageUid' => $subscriptionRequest->_uid,
                'userId' => (string) getUserID(),
            ]
        );

        if ($orderReaction['reaction_code'] == 1) {
            $orderReaction['data']['key_id'] = $this->razorpayEngine->getPublishableKey();
        }

        return $this->processResponse($orderReaction, [], [], true);
    }

    /**
     * POST /api/verify-payment — HMAC verify then activate subscription.
     */
    public function verifyPayment(BaseRequest $request)
    {
        validateVendorAccess('administrative');

        $request->validate([
            'package_uid' => 'required|uuid',
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if (! $this->razorpayEngine->verifyPaymentSignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        )) {
            return $this->processResponse([
                'reaction_code' => 2,
                'message' => __tr('Payment verification failed.'),
            ], [], [], true);
        }

        $processReaction = $this->manualSubscriptionEngine->processRazorpayCheckout([
            'packageUid' => $request->package_uid,
            'razorpayPaymentId' => $request->razorpay_payment_id,
            'paymentVerified' => true,
        ]);

        return $this->processResponse($processReaction, [], [], true);
    }
}
