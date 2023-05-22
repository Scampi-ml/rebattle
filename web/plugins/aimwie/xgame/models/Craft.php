<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class Craft extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_crafts';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'item' => [\Aimwie\Xgame\Models\Item::class],
    ];

    public $hasMany = [
        'items' => [\Aimwie\Xgame\Models\CraftItem::class]
    ];

    public function refreshMetaData()
    {
        $itemsCount = $this->items->count();
        if ($itemsCount) {
            $craftKey = "";
            $itemsIds = [];
            foreach ($this->items as $item) {
                $itemsIds[] = $item->item_id;
            }
            sort($itemsIds);
            $craftKey = implode("_", $itemsIds);
            $this->craft_key = $craftKey;
            $hasDublicate = Craft::where('craft_key', $craftKey)->where('id', '!=', $this->id)->first();
            $this->has_dublicate = $hasDublicate ? true : false;
            $this->items_count = $itemsCount;
        } else {
            $this->craft_key = null;
            $this->has_dublicate = false;
            $this->items_count = 0;
        }
    }

}
