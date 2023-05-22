<?php namespace Aimwie\Xgame\Models;

use Model;
use Redirect;
use ApplicationException;


/**
 * Model
 */
class OpponentItem extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_opponent_items';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'item' => [\Aimwie\Xgame\Models\Item::class],
        'opponent' => [\Aimwie\Xgame\Models\Opponent::class],
    ];

    public function beforeCreate()
    {
        $this->location = $this->item->type_id;
        $checkActiveItem = OpponentItem::where([
            'opponent_id' => $this->opponent_id,
            'location' => $this->location
        ])->first();
        if ($checkActiveItem) {
            $checkActiveItem->no_refresh = 1;
            $checkActiveItem->delete();
        }
    }

    public function afterCreate()
    {
        $this->opponent->hp_max += $this->item->hp;
        $this->opponent->ap_max += $this->item->ap;
        $this->opponent->power += $this->item->power;
        $this->opponent->defense += $this->item->defense;
        $this->opponent->critical += $this->item->critical;
        $this->opponent->buildCaches();
        $this->opponent->save();
    }

    public function beforeUpdate()
    {
        throw ApplicationException('Opponent items can be only added or removed.');
    }

    public function afterDelete()
    {
        $this->opponent->hp_max -= $this->item->hp;
        $this->opponent->ap_max -= $this->item->ap;
        $this->opponent->power -= $this->item->power;
        $this->opponent->defense -= $this->item->defense;
        $this->opponent->critical -= $this->item->critical;
        $this->opponent->buildCaches();
        $this->opponent->save();
    }
}
