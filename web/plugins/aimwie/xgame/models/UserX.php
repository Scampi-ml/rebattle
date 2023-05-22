<?php namespace Aimwie\Xgame\Models;

use RainLab\User\Models\User;
use Aimwie\Xgame\Models\UserItem;

/**
 * Model
 */
class UserX extends User
{
    public $hasMany = [
        'items' => [
            'Aimwie\Xgame\Models\UserItem',
            'key' => 'user_id'
        ]
    ];
    public $jsonable = ['items_cache', 'actions_cache'];

    public function buildCaches()
    {
        $itemsCache = []; $actionsCache = [];
        $locations = UserItem::$userLocations;
        $userItems = UserItem::where('user_id', $this->id)->whereIn('location', $locations)->get();
        if ($userItems->count()) {
            foreach ($userItems as $userItem) {
                $itemsCache[$userItem->location] = [
                    "id" => $userItem->id,
                    "item_id" => $userItem->item_id,
                    "item" => [
                        "id" => $userItem->item->id,
                        "title" => $userItem->item->title,
                        "img_url" => $userItem->item->img_url
                    ]
                ];
                // Actions
                if (count($userItem->item->actions_cache)) {
                    foreach ($userItem->item->actions_cache as $actionId => $actionData) {
                        $actionsCache[$actionId] = $actionData;
                    }
                }
            }
        }
        $this->items_cache = $itemsCache;
        ksort($actionsCache);
        $this->actions_cache = $actionsCache;
    }
}
