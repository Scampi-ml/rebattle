<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserX;
use Log;
use Response;

class StripeWebhookComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'StripeWebhookComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $privateKey = config("re.stripe_private_key");
        $endpoint_secret = config("re.stripe_webhook_secret");
        \Stripe\Stripe::setApiKey($privateKey);

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            return Response::make("Invalid payload", 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return Response::make("Invalid signature", 400);
        }
        
        // Handle the event
        switch ($event->type) {
        case 'checkout.session.completed':

            $data = $event->data->object;

            if (isset($data->payment_status) && $data->payment_status == "paid") {
                // client_reference_id split
                $criSplit = explode("_", $data->client_reference_id);
                $userId = isset($criSplit[0]) ? $criSplit[0] : null;
                $product = isset($criSplit[1]) ? $criSplit[1] : null;
                $quantity = isset($criSplit[2]) ? intval($criSplit[2]) : null;

                // User Index
                $customerId = $data->customer;
                $user = UserX::where('stripe_id', $customerId)->first();
                // Email mode
                if (!$user) {
                    $customerEmail = $data->customer_details->email;
                    $user = UserX::where('email', $customerEmail)->first();
                    if (!$user) {
                        $user = UserX::find($userId);
                    }
                    if (!$user) {
                        echo 'User not found!';
                        http_response_code(400);
                        exit();
                    }
                    $user->stripe_id = $customerId;
                    $user->save();
                }

                // Update Gems/Coins
                if ($product != "gems" && $product != "coins") {
                    echo 'Product not found!';
                    http_response_code(400);
                    exit();
                }
                
                if (!is_integer($quantity) || $quantity < 1 || $quantity > 99) {
                    Log::info(["wrong_quantity", $quantity, $criSplit]);

                    echo 'Wrong quantity!';
                    http_response_code(400);
                    exit();
                }

                if ($product == "gems") {
                    $bundleSize = config('re.gems_bundle_size');
                    $user->gems += $quantity * $bundleSize;
                }
                if ($product == "coins") {
                    $bundleSize = config('re.coins_bundle_size');
                    $user->coins += $quantity * $bundleSize;
                }
                $user->save();

            }

            Log::info([$data]);

            break;
        default:
            // Unexpected event type
            echo 'Received unknown event type';
            http_response_code(400);
        }

        http_response_code(200);
    }
}
