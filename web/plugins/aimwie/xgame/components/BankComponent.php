<?php
namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\BankItem;
use Aimwie\Xgame\Models\Item;
use Auth;
use Redirect;
use Flash;
use Aimwie\Xgame\Models\UserX;
use Validator;

class BankComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'BankComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $userItems = UserItem::where('user_id', $user->id)
            ->where('location', 'storage')
            ->orderby('updated_at', 'asc')
            ->get();

        $safeItems = BankItem::where('user_id', $user->id)
            ->where('location', 'safe')
            ->get();

        // Check pledgeItems To Delte
        $pledgeItemsToDelete = BankItem::where('user_id', $user->id)
            ->where('location', 'pledge')
            ->where('created_at', '>', date("Y-m-d H:i:s", time() + 1728000)) // 20 days
            ->get();
        if ($pledgeItemsToDelete->count()) {
            foreach ($pledgeItemsToDelete as $piDelete) {
                $piDelete->delete();
            }
        }

        $pledgeItems = BankItem::where('user_id', $user->id)
            ->where('location', 'pledge')
            ->get();

        $this->page['userItems'] = $userItems;
        $this->page['safeSlots'] = config('re.safe_slots');
        $this->page['pledgeSlots'] = config('re.pledge_slots');
        $this->page['safeItems'] = $safeItems;
        $this->page['pledgeItems'] = $pledgeItems;

        $this->addJs('/themes/x/assets/x/js/bank.js?ts=');
    }

    public function onAction()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $post = post();
        $action = post('action');

        // PUT User -> Bank
        if ($action == "safe-put" || $action == "pledge-put") {
            $userItem = UserItem::where('user_id', $user->id)
                ->where('id', post('uid'))
                ->first();

            if (!$userItem) {
                return [
                    "ok" => false,
                    "error" => "User item not found!"
                ];
            }
        }

        // TAKE Bank -> User
        if ($action == "safe-take" || $action == "pledge-take") {
            $bankItem = BankItem::where('user_id', $user->id)
                ->where('id', post('bid'))
                ->first();

            if (!$bankItem) {
                return [
                    "ok" => false,
                    "error" => "Bank item not found!"
                ];
            }
        }

        if ($action == "safe-put") {
            if ($userItem->location == "storage") {

                $storagePrice = round($userItem->item->price_coins * 0.05);
                if ($user->coins < $storagePrice) {
                    return [
                        "ok" => false,
                        "error" => "You don't have enough coins"
                    ];
                }

                $countSafeItems = BankItem::where('user_id', $user->id)->where('location', 'safe')->count();

                if ($countSafeItems >= 40) {
                    return [
                        "ok" => false,
                        "error" => "You have reached maximum safe limit!"
                    ];
                }

                $user->coins -= $storagePrice;
                $user->save();

                $bankItem = new BankItem;
                $bankItem->user_id = $user->id;
                $bankItem->item_id = $userItem->item_id;
                $bankItem->location = "safe";
                $bankItem->item_value = $userItem->item->price_coins;
                $bankItem->save();

                $userItem->delete();
            }
            return [
                "ok" => true,
                "refresh" => true
            ];
        }
        if ($action == "safe-take") {
            if ($bankItem->location == "safe") {
                if ($user->storage >= $user->storage_max) {
                    return [
                        "ok" => false,
                        "error" => "You don't have enough inventory storage space"
                    ];
                }
                $userItem = new UserItem;
                $userItem->user_id = $user->id;
                $userItem->item_id = $bankItem->item_id;
                $userItem->location = "storage";
                $userItem->save();

                $bankItem->delete();
            }
            return [
                "ok" => true,
                "refresh" => true
            ];
        }

        if ($action == "pledge-put") {
            if ($userItem->location == "storage") {

                $countPledgeItems = BankItem::where('user_id', $user->id)->where('location', 'pledge')->count();

                if ($countPledgeItems >= 40) {
                    return [
                        "ok" => false,
                        "error" => "You have reached maximum pledge limit!"
                    ];
                }

                $pledgeLoan = round($userItem->item->price_coins * 0.7);
                $user->coins += $pledgeLoan;
                $user->save();

                $bankItem = new BankItem;
                $bankItem->user_id = $user->id;
                $bankItem->item_id = $userItem->item_id;
                $bankItem->location = "pledge";
                $bankItem->item_value = $userItem->item->price_coins;
                $bankItem->save();

                $userItem->delete();
            }
            return [
                "ok" => true,
                "refresh" => true
            ];
        }

        if ($action == "pledge-take") {
            if ($bankItem->location == "pledge") {
                if ($user->storage >= $user->storage_max) {
                    return [
                        "ok" => false,
                        "error" => "You don't have enough inventory storage space"
                    ];
                }

                $pledgePaybackTotal = $bankItem->calcPayback();

                if ($user->coins < $pledgePaybackTotal) {
                    return [
                        "ok" => false,
                        "error" => "You don't have enough coins"
                    ];
                }
                $user->coins -= $pledgePaybackTotal;
                $user->save();

                $userItem = new UserItem;
                $userItem->user_id = $user->id;
                $userItem->item_id = $bankItem->item_id;
                $userItem->location = "storage";
                $userItem->save();

                $bankItem->delete();
            }
            return [
                "ok" => true,
                "refresh" => true
            ];
        }

        return [
            "ok" => true,
            "refresh" => true
        ];
    }
    public function onSendCurrency()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $post = post();

        $rules =
            [
                'player_name' => 'required|min:3|max:20|alpha_num',
                // trim not a OCMS validation rule
                'amount' => 'required|min:1|max:100000',
                'type' => 'required|in:coins,gems',
                'step' => 'required|in:1,2'
            ];
        // 
        if (post('step') == 2) {
            $rules['timestamp'] = 'required|integer';
            $rules['security_key'] = 'required';
        }

        $validator = Validator::make(
            $post,
            $rules

        );
        if ($validator->fails()) {
            return [
                '#bankcomponent-transfer' => "<div class='alert alert-warning mt-2'>" . $validator->messages()->first() . "</div>"
            ];
        }


        $xUser = UserX::where('player_name', $post['player_name'])->first();
        if (!$xUser) {
            return [
                '#bankcomponent-transfer' => "<div class='alert alert-warning mt-2'>User does not exist</div>"
            ];
        }

        $calculatedFee = round($post['amount'] / 100, 0, PHP_ROUND_HALF_UP);

        $fee = $calculatedFee < 10 ? 10 : $calculatedFee;

        $secretKey = sha1(config('app.key') . config('app.url') . "rebattle");

        if ($post['step'] == 1) {
            $timestamp = time();
            $securityKey = sha1($user->id . $secretKey . $post['amount'] . $timestamp);
            return [
                '#bankcomponent-transfer' => $this->renderPartial(
                    '@bankcomponenent-transfer.htm',
                    [
                        'fee' => $fee,
                        'securityKey' => $securityKey,
                        'timestamp' => $timestamp,
                        'xUser' => $post['player_name'],
                        'amount' => $post['amount'],
                        'type' => $post['type']
                    ]
                )
            ];
        }


        if ($post['step'] == 2) {
            if (post('timestamp') < time() - 30) {
                return [
                    '#bankcomponent-transfer' => "<div class='alert alert-warning mt-2'>Timestamp is invalid</div>"
                ];
            }
            // post('timestamp') > (time() - 30)

            $securityKey = sha1($user->id . $secretKey . $post['amount'] . $post['timestamp']);
            if ($securityKey != $post['security_key']) {
                return [
                    '#bankcomponent-transfer' => "<div class='alert alert-warning mt-2'>Security key is invalid</div>"
                ];
            }

            $xUser = UserX::where('player_name', $post['player_name'])->first();
            if (!$xUser) {
                return [
                    '#bankcomponent-transfer' => "<div class='alert alert-warning mt-2'>User does not exist</div>"
                ];
            }

            $calculatedFee = round($post['amount'] / 100, 0, PHP_ROUND_HALF_UP);

            $fee = $calculatedFee < 10 ? 10 : $calculatedFee;
            $inTotal = $post['amount'] + $fee;
            $currencyType = $post['type'] == "gems" ? "gems" : "coins";

            if ($user->{$currencyType} < $inTotal) {
                return [
                    '#transfer-modal-alert' => "<div class='alert alert-warning mt-2'>You don't have enough " . $currencyType . "</div>",
                    '#user-main-coins' => $user->coins,
                    '#user-main-gems' => $user->gems
                ];
            }
            $user->{$currencyType} -= $inTotal;
            $user->save();

            $xUser->{$currencyType} += $post['amount'];
            $xUser->save();

        }

        Flash::success("You have sent " . $post['amount'] . " " . $currencyType . " to " . $post['player_name']);
        return Redirect::to('/bank');
    }
}