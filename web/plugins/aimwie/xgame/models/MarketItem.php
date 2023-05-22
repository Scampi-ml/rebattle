<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class MarketItem extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_market_items';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'item' => [\Aimwie\Xgame\Models\Item::class],
        'user' => [\Aimwie\Xgame\Models\UserX::class],
    ];

    public function afterCreate()
    {
        $this->item->in_market += 1;
        $this->item->save();
    }

    public function afterDelete()
    {
        $this->item->in_market -= 1;
        $this->item->save();
    }
}
