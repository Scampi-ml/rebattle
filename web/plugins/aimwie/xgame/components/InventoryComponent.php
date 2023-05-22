<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\Fight;
use Auth;
use Redirect;
use Flash;

class InventoryComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'InventoryComponent Component',
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

        $this->page['userItems'] = $userItems;

        $this->addJs('/themes/x/assets/x/js/inventory.js?ts=');
    }

    public function onAction()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $post = post();
        $userItem = UserItem::where('user_id', $user->id)
            ->where('id', post('uid'))
            ->first();

        if (!$userItem) {
            return Redirect::to('/404');
        }

        $action = post('action');

        if ($action == "take-on" || $action == "take-off") {
            $fight = Fight::where('user_id', $user->id)->first();
            if ($fight) {
                return [
                    "ok" => false,
                    "error" => "You can not change your wearable items while you are in a fight!"
                ];
            }
        }

        if ($action == "take-on") {
            if ($userItem->location == "storage") {
                if (!in_array($userItem->item->type_id, UserItem::$userLocations) ) {
                    return [
                        "ok" => false,
                        "error" => "This is not wearable item! Maybe it's craft material!?"
                    ];
                }
                if ($userItem->item->level > $user->level) {
                    return [
                        "ok" => false,
                        "error" => "You have to reach level ".$userItem->level." to wear this item"
                    ];
                }

                $alreadyItem = UserItem::where('user_id', $user->id)
                    ->where('location', $userItem->item->type_id)
                    ->first();
                if ($alreadyItem) {
                    if ($alreadyItem->item->storage_max) {
                        $storageLeft = $user->storage_max - $user->storage;
                        $storageLose = $alreadyItem->item->storage_max;
                        $storageGet = $userItem->item->storage_max;
                        if (($storageLeft + $storageGet) < $alreadyItem->item->storage_max) {
                            return [
                                "ok" => false,
                                "error" => "You can not switch items, need more free storage space!"
                            ];
                        }
                    }

                    $alreadyItem->location = "storage";
                    $alreadyItem->save();
                }

                $userItem->location = $userItem->item->type_id;
                $userItem->save();
                return Redirect::to('/inventory');
            }
        }
        if ($action == "take-off") {
            if ($userItem->location != "storage" && $userItem->location == $userItem->item->type_id) {
                if ($user->storage >= $user->storage_max) {
                    return [
                        "ok" => false,
                        "error" => "You don't have enough inventory storage space"
                    ];
                }
                if ($userItem->item->storage_max) {
                    $storageLeft = $user->storage_max - $user->storage;
                    $storageLose = $userItem->item->storage_max;
                    if ($storageLeft < ($userItem->item->storage_max + 1)) { // +1 because take off
                        return [
                            "ok" => false,
                            "error" => "You can not take of this item, need more free storage space!"
                        ];
                    }
                }


                $userItem->location = "storage";
                $userItem->save();
            }
            return Redirect::to('/inventory');
        }

        if ($action == "delete") {
            if ($userItem->location == "storage") {
                
                $userItem->delete();
            }
            Flash::success('Item deleted!');
            return Redirect::to('/inventory');
        }
        
    }
}
