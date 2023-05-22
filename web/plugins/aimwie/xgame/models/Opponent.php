<?php namespace Aimwie\Xgame\Models;

use Model;
use Cache;

/**
 * Model
 */
class Opponent extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_opponents';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $jsonable = ['items_cache', 'actions_cache'];

    public $belongsTo = [
        'avatar' => [\Aimwie\Xgame\Models\Avatar::class],
    ];

    public $hasMany = [
        'items' => [\Aimwie\Xgame\Models\OpponentItem::class],
        'rewards' => [\Aimwie\Xgame\Models\OpponentReward::class]
    ];


    public function afterSave()
    {
        if ($this->avatar_mode == "avatar") {
            if ($this->avatar) {
                $thumbUrl = $this->avatar->img_url;
            } else {
                $thumbUrl = "";
            }
        } else {
            if ($this->img) {
                $thumbUrl = str_replace(config('app.url'), "", $this->img->getThumb(100, 150, ['mode' => 'crop']));
            } else {
                $thumbUrl = "";
            }
        }
        if ($this->img_url !== $thumbUrl) {
            $this->img_url = $thumbUrl;
            $this->save();
        }

    }

    public function buildCaches()
    {
        $itemsCache = []; $actionsCache = [];
        $opponentItems = $this->items;
        if ($opponentItems->count()) {
            foreach ($opponentItems as $opponentItem) {
                $itemsCache[$opponentItem->location] = [
                    "id" => $opponentItem->id,
                    "item_id" => $opponentItem->item_id,
                    "item" => [
                        "id" => $opponentItem->item->id,
                        "title" => $opponentItem->item->title,
                        "img_url" => $opponentItem->item->img_url
                    ]
                ];
                if (count($opponentItem->item->actions_cache)) {
                    foreach ($opponentItem->item->actions_cache as $actionId => $actionData) {
                        $actionsCache[$actionId] = $actionData;
                    }
                }
            }
        }
        $this->items_cache = $itemsCache;
        ksort($actionsCache);
        $this->actions_cache = $actionsCache;
    }

    public function buildRewardData()
    {
        // TODO
        $rewards = $this->rewards;
        $rewardsCount = $rewards->count();
        $rewardsSrSum = 0;
        if ($rewardsCount) {
            foreach ($rewards as $reward) {
                $rewardsSrSum += $reward->success_rate;
                $reward->success_rate_position = $rewardsSrSum;
                $reward->save();
            }
        }
        $this->rewards_count = $rewardsCount;
        $this->rewards_sr_sum = $rewardsSrSum;
    }
}
