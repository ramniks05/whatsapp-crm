<?php
/**
 * WhatsJet
 *
 * This file is part of the WhatsJet software package developed and licensed by livelyworks.
 *
 * You must have a valid license to use this software.
 *
 * © 2024 - 2026 livelyworks. All rights reserved.
 * Redistribution or resale of this file, in whole or in part, is prohibited without prior written permission from the author.
 *
 * For support or inquiries, contact: contact@livelyworks.net
 *
 * @package     WhatsJet
 * @author      livelyworks <contact@livelyworks.net>
 * @copyright   Copyright (c) 2024 - 2026 livelyworks
 * @website     https://livelyworks.net
 */


namespace App\Yantrana\Components\Subscription\PaymentEngines;

use App\Yantrana\Base\BaseEngine;
use Razorpay\Api\Api as RazorpayAPI;
use Razorpay\Api\Errors\BadRequestError;
use Illuminate\Support\Str;


/**
 * This MailService class for manage globally -
 * mail service in application.
 *---------------------------------------------------------------- */
class RazorpayEngine extends BaseEngine
{
    protected $razorpayAPI;

    protected $webhookSecret;

    protected string $publishableKey = '';

    /**
     * Constructor.
     *
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
        [$razorpayKey, $razorpaySecret, $this->webhookSecret] = $this->resolveApiCredentials();
        $this->publishableKey = (string) $razorpayKey;
        $this->razorpayAPI = new RazorpayAPI($razorpayKey, $razorpaySecret);
    }

    /**
     * Prefer .env credentials; fall back to super-admin payment settings.
     */
    protected function resolveApiCredentials(): array
    {
        $envKey = config('services.razorpay.key_id');
        $envSecret = config('services.razorpay.key_secret');
        if (! __isEmpty($envKey) && ! __isEmpty($envSecret)) {
            return [
                $envKey,
                $envSecret,
                config('services.razorpay.webhook_secret') ?: '',
            ];
        }
        if (getAppSettings('use_test_razorpay')) {
            return [
                getAppSettings('razorpay_testing_publishable_key'),
                getAppSettings('razorpay_testing_secret_key'),
                getAppSettings('razorpay_testing_webhook_secret'),
            ];
        }

        return [
            getAppSettings('razorpay_live_publishable_key'),
            getAppSettings('razorpay_live_secret_key'),
            getAppSettings('razorpay_live_webhook_secret'),
        ];
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    /**
     * Create Razorpay order (Standard Checkout step 1).
     */
    public function createOrder(int $amountPaise, string $currency, string $receipt, array $notes = [])
    {
        try {
            $order = $this->razorpayAPI->order->create([
                'amount' => $amountPaise,
                'currency' => $currency,
                'receipt' => Str::limit($receipt, 40, ''),
                'notes' => $notes,
            ]);

            return $this->engineReaction(1, [
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
            ], __tr('Order created.'));
        } catch (BadRequestError $e) {
            $httpStatus = str_contains(strtolower($e->getMessage()), 'auth') ? 401 : 400;

            return $this->engineReaction(2, [
                'http_status' => $httpStatus,
                'errorMessage' => $e->getMessage(),
            ], $e->getMessage());
        } catch (\Exception $e) {
            return $this->engineReaction(2, [
                'http_status' => 500,
                'errorMessage' => $e->getMessage(),
            ], $e->getMessage());
        }
    }

    /**
     * Verify payment signature (Standard Checkout step 3).
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        try {
            $this->razorpayAPI->utility->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

            return true;
        } catch (\Exception $e) {
            __logDebug($e->getMessage());

            return false;
        }
    }

    /**
     * This method use for capturing payment.
     *
     * @param  string  $paymentId
     * @return paymentReceived
     *---------------------------------------------------------------- */
    public function capturePayment($paymentId)
    {
        try {
            // fetch a particular payment
            $payment = $this->razorpayAPI->payment->fetch($paymentId);

            // Captures a payment
            $paymentReceived = $this->razorpayAPI->payment->fetch($paymentId)->capture(['amount' => $payment['amount']]);

            return $this->engineReaction(1, [
                'transactionDetail' => $paymentReceived->toArray(),
            ], __tr('Complete'));
        } catch (\Exception $e) {
            return $this->engineReaction(2, [
                'errorMessage' => 'Invalid Api Key',
            ], $e->getMessage());
        }
    }

    /**
     * get payment response by webhook
     *
     * @return  array
     *-------------------------------------*/
    public function paymentWebhook()
    {
        $this->razorpayAPI;

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
        $event = $paymentIntent = null;

        try {
            $this->razorpayAPI->utility->verifyWebhookSignature($payload, $sig_header, $this->webhookSecret);
            $event = json_decode($payload, true);
        } catch (\Errors\SignatureVerificationError $e) {
            // Invalid signature
            __logDebug($e->getMessage());
            http_response_code(400);
            exit();
        }

        switch ($event['event']) {
            case 'payment.captured':
                $paymentIntent = $event;
                break;

            default:
                echo 'Received unknown event type ' . $event['event'];
                break;
        }
        http_response_code(200);
        return $this->engineReaction(1, ['paymentIntent' => $paymentIntent]);
    }
}
