<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\Craft;
use Aimwie\Xgame\Models\CraftKeys;
use Auth;
use Redirect;
use Cache;
use Flash;

class CraftComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'CraftComponent Component',
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

        $craft = null;
        $selected = Cache::get('craft_select_'.$user->id, []);
        $selectedKeys = array_keys($selected);
        $craftItems = UserItem::where('user_id', $user->id)
            ->where('location', 'storage')
            ->whereIn('id', $selectedKeys)
            ->orderby('updated_at', 'asc')
            ->get();

        $selectedCount = count($selected);
        if ($selectedCount) {
            if ($craftItems->count() == $selectedCount) {
                $sortSelected = $selected;
                sort($sortSelected);
                $craftKey = implode("_", $sortSelected);
                $craft = Craft::where('craft_key', $craftKey)->first();
            } else {
                Cache::put('craft_select_'.$user->id, [], 3600);
                $selected = [];
                $craftItems = [];
            }
        }

        $this->page['userItems'] = $userItems;
        $this->page['craftItems'] = $craftItems;
        $this->page['selected'] = $selected;
        $this->page['craft'] = $craft;

        $this->addJs('/themes/x/assets/x/js/craft.js?ts=');
    }

    public function onAction()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $post = post();
        $action = post('action');

        if ($action == "craft-select" || $action == "craft-unselect") {
            $userItem = UserItem::where('user_id', $user->id)
                ->where('id', post('uid'))
                ->first();

            if (!$userItem) {
                return Redirect::to('/404');
            }
        }

        if ($action == "craft-try") {
            $selected = Cache::get('craft_select_'.$user->id, []);
            $selectedCount = count($selected);
            if ($selectedCount) {
                $sortSelected = $selected;
                sort($sortSelected);
                $craftKey = implode("_", $sortSelected);
                $craft = Craft::where('craft_key', $craftKey)->first();
                if ($craft) {
                    $selectedKeys = array_keys($selected);
                    $craftMaterials = UserItem::where('user_id', $user->id)
                        ->where('location', 'storage')
                        ->whereIn('id', $selectedKeys)
                        ->orderby('updated_at', 'asc')
                        ->get();
                    if ($craftMaterials->count() === $selectedCount) {
                        if ($craft->level > $user->level) {
                            Flash::warning("To craft this item you must reach level ".$craft->level);
                            return Redirect::to('/craft');
                        }
                        if ($craft->coins > $user->coins) {
                            Flash::warning("You don't have enough coins!");
                            return Redirect::to('/craft');
                        }
                        if ($craft->gems > $user->gems) {
                            Flash::warning("You don't have enough gems!");
                            return Redirect::to('/craft');
                        }
                        if ($craft->coins || $craft->gems) {
                            if ($craft->coins) { $user->coins -= $craft->coins; }
                            if ($craft->gems) { $user->gems -= $craft->gems; }
                            $user->save();
                        }

                        // Delete items...
                        foreach ($craftMaterials as $craftMaterial) {
                            $craftMaterial->delete();
                        }
                        Cache::put('craft_select_'.$user->id, [], 3600);
                        $chance = rand(1, 100);
                        if ($chance > $craft->success_rate) {
                            Flash::error("Sorry, you lost your materials!");
                        } else {
                            $userItem = new UserItem;
                            $userItem->user_id = $user->id;
                            $userItem->item_id = $craft->item_id;
                            $userItem->location = "storage";
                            $userItem->save();
                            Flash::success("Nice work, you crafted a new item!");
                        }
                    }
                }
            }
            return Redirect::to('/craft');
        }

        if ($action == "craft-unselect-all") {
            Cache::put('craft_select_'.$user->id, [], 3600);
            return Redirect::to('/craft');
        }
        if ($action == "craft-select") {
            if ($userItem->location == "storage") {

                $selected = Cache::get('craft_select_'.$user->id, []);
                if (count($selected) >= 8) {
                    return [
                        "ok" => false,
                        "error" => "You can select only 8 crafts!"
                    ];
                }

                $selected[$userItem->id] = $userItem->item_id;
                Cache::put('craft_select_'.$user->id, $selected, 3600);

                return Redirect::to('/craft');
            }
        }
        if ($action == "craft-unselect") {
            $selected = Cache::get('craft_select_'.$user->id, []);
                
            if (isset($selected[$userItem->id])) {
                unset($selected[$userItem->id]);
            }

            Cache::put('craft_select_'.$user->id, $selected, 3600);

            return Redirect::to('/craft');
        }
        
    }

}
