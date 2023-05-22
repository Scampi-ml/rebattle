<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class CraftItem extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_craft_items';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'item' => [\Aimwie\Xgame\Models\Item::class],
        'craft' => [\Aimwie\Xgame\Models\Craft::class],
    ];

    public function afterSave()
    {
        $this->craft->refreshMetaData();
        $this->craft->save();
    }
    public function afterDelte()
    {
        $this->craft->refreshMetaData();
        $this->craft->save();
    }
}
