<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class UserItem extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public static $userLocations = [
        "weapon",
        "shield",
        "helmet",
        "armor",
        "pants",
        "boots",
        "bag"
    ];
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_user_items';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'item' => [\Aimwie\Xgame\Models\Item::class],
        'user' => [\Aimwie\Xgame\Models\UserX::class]
    ];

    public function afterCreate()
    {
        if ($this->location == "storage") {
            $this->user->storage += 1;
            $this->user->save();
        }
        $this->item->in_game += 1;
        $this->item->save();
    }

    public function afterDelete()
    {
        if ($this->location == "storage") {
            $this->user->storage -= 1;
            $this->user->save();
        }
        $this->item->in_game -= 1;
        $this->item->save();
    }

    public function afterUpdate()
    {
        $dirty = $this->getDirty();
        $original = $this->getOriginal();
        $actionLocations = ["storage", "bank", "craft"];
        if (isset($dirty['location'])) {
            $item = $this->item;
            // Take on
            if (!in_array($dirty['location'], $actionLocations) && $original['location'] === "storage") {
                if ($item->hp) {
                    $this->user->hp_max += $item->hp;
                    if ($this->user->hp < $this->user->hp_max) {
                        $this->user->hp = $this->user->hp < 0 ? 0 : $this->user->hp;
                        $tsDiff = round(($this->user->hp_max - $this->user->hp) * config('re.hp_rate'));
                        $this->user->hp_ts = time() + $tsDiff;
                    }
                }
                if ($item->ap) {
                    $this->user->ap_max += $item->ap;
                    if ($this->user->ap < $this->user->ap_max) {
                        $this->user->ap = $this->user->ap < 0 ? 0 : $this->user->ap;
                        $tsDiff = round(($this->user->ap_max - $this->user->ap) * config('re.ap_rate'));
                        $this->user->ap_ts = time() + $tsDiff;
                    }
                }

                $this->user->power += $item->power;
                $this->user->defense += $item->defense;
                $this->user->critical += $item->critical;
                $this->user->storage_max += $item->storage_max;
                $this->user->storage -= 1;
                $this->user->buildCaches();
                $this->user->save();
                $this->user->save();
            }
            // Take off
            if ($dirty['location'] === "storage" && !in_array($original['location'], $actionLocations)) {
                if ($item->hp) {
                    $this->user->hp_max -= $item->hp;
                    if ($this->user->hp > $this->user->hp_max) { $this->user->hp = $this->user->hp_max; } // More than MAX
                    if ($this->user->hp < $this->user->hp_max) {
                        $this->user->hp = $this->user->hp < 0 ? 0 : $this->user->hp;
                        $tsDiff = round(($this->user->hp_max - $this->user->hp) * config('re.hp_rate'));
                        $this->user->hp_ts = time() + $tsDiff;
                    }
                }
                if ($item->ap) {
                    $this->user->ap_max -= $item->ap;
                    if ($this->user->ap > $this->user->ap_max) { $this->user->ap = $this->user->ap_max; } // More than MAX
                    if ($this->user->ap < $this->user->ap_max) {
                        $this->user->ap = $this->user->ap < 0 ? 0 : $this->user->ap;
                        $tsDiff = round(($this->user->ap_max - $this->user->ap) * config('re.ap_rate'));
                        $this->user->ap_ts = time() + $tsDiff;
                    }
                }

                $this->user->power -= $item->power;
                $this->user->defense -= $item->defense;
                $this->user->critical -= $item->critical;
                $this->user->storage_max -= $item->storage_max;
                $this->user->storage += 1;
                $this->user->buildCaches();
                $this->user->save();
                $this->user->save();
            }
        }
    }

    
}
