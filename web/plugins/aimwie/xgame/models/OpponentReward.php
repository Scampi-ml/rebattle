<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class OpponentReward extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_opponent_rewards';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'opponent' => [\Aimwie\Xgame\Models\Opponent::class],
        'item' => [\Aimwie\Xgame\Models\Item::class]
    ];

    public function afterCreate()
    {
        $this->opponent->buildRewardData();
        $this->opponent->save();
    }
    public function afterUpdate()
    {
        // To prevent inf. loop from reward data build
        $dirty = $this->getDirty();
        if (count($dirty) < 1 || (count($dirty) == 1 && isset($dirty['success_rate_position']))) {
            return true;
        }

        $this->opponent->buildRewardData();
        $this->opponent->save();
    }
    public function afterDelete()
    {
        $this->opponent->buildRewardData();
        $this->opponent->save();
    }
}
