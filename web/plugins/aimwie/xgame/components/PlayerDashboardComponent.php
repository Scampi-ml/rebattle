<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserX;
use Auth;
use Flash;
use Redirect;
use Validator;

class PlayerDashboardComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'playerDashboardComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $isCoinsEnabled = config("re.coins_price_id") == "" ? false : true;
        $isGemsEnabled = config("re.gems_price_id") == "" ? false : true;
        $isShopEnabled = $isCoinsEnabled || $isGemsEnabled ? true : false;

        $this->page['is_coins_enabled'] = $isCoinsEnabled;
        $this->page['is_gems_enabled'] = $isGemsEnabled;
        $this->page['is_shop_enabled'] = $isShopEnabled;
        
        $this->page['is_shop_enabled'] = $isShopEnabled;


        $this->page["coins_bundle_price"] = config("re.coins_bundle_price");
        $this->page["coins_bundle_size"] = config("re.coins_bundle_size");
        $this->page["gems_bundle_price"] = config("re.gems_bundle_price");
        $this->page["gems_bundle_size"] = config("re.gems_bundle_size");

        $this->page['renew_hp_gems'] = config('re.renew_hp_gems');
        $this->page['renew_ap_gems'] = config('re.renew_ap_gems');
        $this->addJs('/themes/x/assets/x/js/playerdashboard.js?ts=');
    }

    public function onAction()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $action = post('action');
        if ($action == "renew") {
            $renew = post("renew");
            $renew = $renew == "hp" ? "hp" : "ap";
            $renewCost = config('re.renew_'.$renew.'_gems');

            if ($renew == "hp" && $user->hp >= $user->hp_max) {
                Flash::warning("You already have renewed HP!");
                return Redirect::to('/user');
            }
            if ($renew == "ap" && $user->ap >= $user->ap_max) {
                Flash::warning("You already have renewed HP!");
                return Redirect::to('/user');
            }
            
            if ($renewCost > $user->gems) {
                Flash::warning("You don't have enough gems!");
                return Redirect::to('/user');
            }

            if ($renew == "hp") {
                $user->hp = $user->hp_max;
                $user->hp_ts = null;
            }
            if ($renew == "ap") {
                $user->ap = $user->ap_max;
                $user->ap_ts = null;
            }
            $user->gems -= $renewCost;
            $user->save();

            Flash::success("You have renewed ".$renew."!");
            return Redirect::to('/user');
        }
    }

    public function onProfileAddPoint()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $post = post();
        $validator = Validator::make($post, [
            'add_hp' => 'required|integer|min:0|max:1000',
            'add_ap' => 'required|integer|min:0|max:1000',
            'add_power' => 'required|integer|min:0|max:1000',
            'add_defense' => 'required|integer|min:0|max:1000',
        ]);
        if ($validator->fails()) {
            foreach ($validator->messages()->all(':message') as $message) {
                Flash::error($message);
                return Redirect::to('/');
            }
        }

        $addHp = post('add_hp');
        $addAp = post('add_ap');
        $addPower = post('add_power');
        $addDefense = post('add_defense');
        $addTotal = $addHp + $addAp + $addPower + $addDefense;

        if ($addTotal > $user->add_points) {
            Flash::error("You selected to much points!");
            return Redirect::to('/');
        }

        $user->hp_max += $addHp;
        $user->ap_max += $addAp;
        $user->power += $addPower;
        $user->defense += $addDefense;
        $user->add_points -= $addTotal;
        $user->save();

        Flash::success("You added ".$addTotal." points!");
        return Redirect::to('/');
    }

    public function onProfileUpdate()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $rules = [
            'player_name' => 'required|min:5|max:10|regex:/(^[a-z0-9]+$)+/'
        ];

        $messages = [
            'regex' => 'You can only use alphanumeric (a-z0-9)!',
        ];
        $post = [
            "player_name" => mb_strtolower(post('player_name'))
        ];

        $validator = Validator::make($post, $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->messages()->all(':message') as $message) {
                Flash::error($message);
                return Redirect::to('/user');
            }
        }

        $checkExist = UserX::where('player_name', $post['player_name'])->where('id', '!=', $user->id)->first();
        if ($checkExist) {
            Flash::error("Sorry ".$post['player_name']." already taken!");
            return Redirect::to('/user');
        }


        $user->player_name = $post['player_name'];
        $user->save();
        Flash::success("You changed your player name to: ".$user->player_name);
        return Redirect::to('/user');
    }

    public function onCustomerPortal()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        if ($user->stripe_id == null || $user->stripe_id == "") {
            Flash::error("You dont have access to Customer Portal (stripe_id)!");
            return Redirect::to("/user");
        }

        $privateKey = config('re.stripe_private_key');

        $stripe = new \Stripe\StripeClient($privateKey);
        $customerPortalSession = $stripe->billingPortal->sessions->create([
        'customer' => $user->stripe_id,
        'return_url' => config('app.url')."/user",
        ]);

        return Redirect::to($customerPortalSession->url);
    }

    public function onMakeOrder()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $publicKey = "pk_test_thPGjDz9JNxxG8PhNJxN52ky";
        $privateKey = config('re.stripe_private_key');
        \Stripe\Stripe::setApiKey($privateKey);

        $appUrl = config('app.url');

        $product = post("product");
        $quantity = post("quantity");

        $rules = [
            "product" => "required|in:gems,coins",
            "quantity" => "required|integer|min:1|max:99",
        ];

        $validator = Validator::make(post(), $rules);
        if ($validator->fails()) {
            foreach ($validator->messages()->all(":message") as $message) {
                Flash::error($message);
                return Redirect::to("/");
            }
        }

        $priceId = "";
        if ($product == "coins") {
            $priceId = config("re.coins_price_id");
        }
        if ($product == "gems") {
            $priceId = config("re.gems_price_id");
        }
        if ($priceId == "") {
            Flash::error("This product is disbled");
            return Redirect::to("/");
        }
        $sessionData = [
            "line_items" => [[
                # TODO: replace this with the `price` of the product you want to sell
                "price" => $priceId,
                "quantity" => $quantity,
            ]],
            "payment_method_types" => [
                "card",
            ],
            "mode" => "payment",
            "client_reference_id" => $user->id."_".$product."_".$quantity,
            "success_url" => $appUrl . "/checkout-status/success",
            "cancel_url" => $appUrl . "/checkout-status/cancel",
        ];
        if ($user->stripe_id != null && $user->stripe_id != "") {
            $sessionData["customer"] = $user->stripe_id;
        } else {
            $sessionData["customer_email"] = $user->email;
        }

        $checkout_session = \Stripe\Checkout\Session::create($sessionData);

        return Redirect::to($checkout_session->url);
    }
}
