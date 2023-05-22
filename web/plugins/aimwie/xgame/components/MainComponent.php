<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserX;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\BankItem;
use Aimwie\Xgame\Models\MarketItem;
use Auth;
use Cache;

class MainComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'MainComponenet Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['gameDemo'] = config('re.game_demo');
        $this->page['appName'] = config('app.name');
        // TODO: player init screen check other global thigs...
        $user = Auth::getUser();
        if ($user) {
            if ($user->hp_ts && $user->hp_ts <= time()) {
                $user->hp_ts = null;
                $user->hp = $user->hp_max;
                $user->save();
            }
            if ($user->ap_ts && $user->ap_ts <= time()) {
                $user->ap_ts = null;
                $user->ap = $user->ap_max;
                $user->save();
            }
            $user->actions_cache = json_decode($user->actions_cache, true);
            $user->items_cache = json_decode($user->items_cache);
        }
        $this->page['user'] = $user;
        $this->page['time'] = time();

        // $this->page['userX'] = $userX;
    }
    public function onShowItem()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $post = post();
        $mode = post('mode');
        $userItem = null;
        $bankItem = null;
        $marketItem = null;
        if (post('uid')) {
            $userItem = UserItem::where('user_id', $user->id)
                ->where('id', post('uid'))
                ->first();
            if (!$userItem) {
                return Redirect::to('/404');
            }
            $item = Item::find($userItem->item_id);
            if ($mode == "bank_inventory") {
                $userItem->safe_coins = round($item->price_coins*0.05);
                $userItem->pledge_loan = round($item->price_coins*0.7);
                $userItem->pledge_pay = round($item->price_coins*0.8);
                $userItem->pledge_pay_day = round($item->price_coins*0.01);
            }
            if ($mode == "craft_inventory") {
                $selected = Cache::get('craft_select_'.$user->id, []);
                $userItem->is_selected = isset($selected[$userItem->id]) ? true : false;
            }
        } else if (post('bid')) {
            $bankItem = BankItem::where('user_id', $user->id)
                ->where('id', post('bid'))
                ->first();
            if (!$bankItem) {
                return Redirect::to('/404');
            }
            $item = Item::find($bankItem->item_id);
            if ($mode == "bank_pledge") {
                $bankItem->pledge_days = $bankItem->calcDays();
                $bankItem->pledge_payback = $bankItem->calcPayback();
            }
        } else if (post('mid')) {
            if ($mode == "market_sell") {
                $marketItem = MarketItem::where('user_id', $user->id)
                    ->where('id', post('mid'))
                    ->first();
            }
            if ($mode == "market_buy") {
                $marketItem = MarketItem::where('id', post('mid'))
                    ->first();
            }
            if (!$marketItem) {
                return Redirect::to('/404');
            }
            $item = Item::find($marketItem->item_id);
        } else {
            $item = Item::find(post('id'));
        }
        
        return [
            "ok" => true,
            "item" => $item,
            "userItem" => $userItem,
            "bankItem" => $bankItem,
            "marketItem" => $marketItem,
        ];
    }
}
