<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\MarketItem;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\UserX;
use Auth;
use Flash;
use Redirect;
use Validator;

class MarketComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'MarketComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun() {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $section = $this->param('section', 'buy');
        if (!in_array($section, ['buy', 'sell', 'my'])) {
            $section = "buy";
        }
        $itemList = null; $itemId = null;
        if ($section == "sell") {
            $userItems = UserItem::where('user_id', $user->id)
                ->where('location', 'storage')
                ->orderby('updated_at', 'asc')
                ->get();

            $this->page['userItems'] = $userItems;

            $marketItems = MarketItem::where('user_id', '=', $user->id)
                ->orderby('id', 'asc')
                ->get();
        } else {
            $itemId = isset($_GET['item_id']) ? $_GET['item_id'] : null;
            if ($itemId) {
                $marketItems = MarketItem::where('item_id', $itemId)
                    ->orderby('price_coins', 'asc')
                    ->orderby('price_gems', 'asc')
                    ->paginate(20);
            } else {
                $marketItems = MarketItem::orderby('price_coins', 'asc')
                    ->orderby('price_gems', 'asc')
                    ->paginate(20);
            }
            $itemList = Item::orderby('type_id', 'asc')->get();
        }
            
        $this->page['itemList'] = $itemList;
        $this->page['marketItems'] = $marketItems;
        $this->page['section'] = $section;
        $this->page['sellSlots'] = config("re.sell_slots");
        $this->page['itemId'] = $itemId;

        $this->addJs('/themes/x/assets/x/js/market.js?ts=');
    }

    public function onAction()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $post = post();
        $action = post('action');


        if ($action == "market-buy") {
            $marketItem = MarketItem::find(post('mid'));
            if (!$marketItem) {
                return Flash::danger('Item not found!');
            }

            if ($marketItem->price_coins > $user->coins) {
                Flash::warning("You don't have enough coins!");
                return Redirect::to('/market');
            }
            if ($marketItem->price_gems > $user->gems) {
                Flash::warning("You don't have enough gems!");
                return Redirect::to('/market');
            }
            if ($user->storage >= $user->storage_max) {
                Flash::warning("You don't have enough inventory storage space!");
                return Redirect::to('/market');
            }

            if ($marketItem->price_coins) {
                $user->coins -= $marketItem->price_coins;
            }
            if ($marketItem->price_gems) {
                $user->gems -= $marketItem->price_gems;
            }
            $user->save();

            $userItem = new UserItem;
            $userItem->user_id = $user->id;
            $userItem->item_id = $marketItem->item_id;
            $userItem->location = "storage";
            $userItem->save();

            if ($marketItem->user_id) {
                $seller = UserX::find($marketItem->user_id);
                if ($seller) {
                    if ($marketItem->price_coins) {
                        $seller->coins += $marketItem->price_coins;
                    }
                    if ($marketItem->price_gems) {
                        $seller->gems += $marketItem->price_gems;
                    }
                    $seller->save();
                }
            }
            
            $marketItem->delete();

            Flash::success("You bought item!");
            return Redirect::to('/market');
        }

        if ($action == "market-sell") {
            $userItem = UserItem::where('id', post('uid'))->where('user_id', $user->id)->first();
            if (!$userItem) {
                return [
                    "ok" => false,
                    "error" => "Item not found!"
                ];
            }

            $rules = [
                'price_coins' => 'required|integer|min:0|max:10000000',
                'price_gems' => 'required|integer|min:0|max:1000000',
            ];
    
            $validator = Validator::make(post(), $rules);
            if ($validator->fails()) {
                foreach ($validator->messages()->all(':message') as $message) {
                    return [
                        "ok" => false,
                        "error" => $message
                    ];
                }
            }

            if (post('price_coins') < 1 && post('price_gems') < 1) {
                return [
                    "ok" => false,
                    "error" => "Please set price for the item!"
                ];
            }

            $marketItemCount = MarketItem::where('user_id', $user->id)->count();
            if ($marketItemCount >= config("re.sell_slots")) {
                return [
                    "ok" => false,
                    "error" => "You have reached maximum market limit!".$marketItemCount
                ];
            }

            $marketItem = new MarketItem();
            $marketItem->user_id = $user->id;
            $marketItem->item_id = $userItem->item_id;
            $marketItem->price_coins = post('price_coins');
            $marketItem->price_gems = post('price_gems');
            $marketItem->save();

            $userItem->delete();

        }

        if ($action == "market-update") {
            $marketItem = MarketItem::where('id', post('mid'))->where('user_id', $user->id)->first();
            if (!$marketItem) {
                return [
                    "ok" => false,
                    "error" => "Item not found!"
                ];
            }

            $rules = [
                'price_coins' => 'required|integer|min:0|max:10000000',
                'price_gems' => 'required|integer|min:0|max:1000000',
            ];
    
            $validator = Validator::make(post(), $rules);
            if ($validator->fails()) {
                foreach ($validator->messages()->all(':message') as $message) {
                    return [
                        "ok" => false,
                        "error" => $message
                    ];
                }
            }

            if (post('price_coins') < 1 && post('price_gems') < 1) {
                return [
                    "ok" => false,
                    "error" => "Please set price for the item!"
                ];
            }

            $marketItem->price_coins = post('price_coins');
            $marketItem->price_gems = post('price_gems');
            $marketItem->save();

            Flash::success("Item updated!");
            return Redirect::to('/market/sell');
        }
        if ($action == "market-inventory") {
            $marketItem = MarketItem::where('id', post('mid'))->where('user_id', $user->id)->first();
            if (!$marketItem) {
                return [
                    "ok" => false,
                    "error" => "Item not found!"
                ];
            }
            if ($user->storage >= $user->storage_max) {
                return [
                    "ok" => false,
                    "error" => "You don't have enough inventory storage space!"
                ];
            }

            $userItem = new UserItem;
            $userItem->user_id = $user->id;
            $userItem->item_id = $marketItem->item_id;
            $userItem->location = "storage";
            $userItem->save();

            $marketItem->delete();

            Flash::success("Item moved back to inventory!");
            return Redirect::to('/market/sell');
        }


        return [
            "ok" => true,
            "refresh" => true
        ];
    }

}
