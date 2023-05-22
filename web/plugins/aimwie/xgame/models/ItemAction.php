<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class ItemAction extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_item_actions';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'item' => [\Aimwie\Xgame\Models\Item::class],
        'action' => [\Aimwie\Xgame\Models\Action::class],
    ];

    public function afterSave()
    {
        $this->item->actionsCacheBuild();
        $this->item->save();
    }
    public function afterDelete()
    {
        $this->item->actionsCacheBuild();
        $this->item->save();
    }
}
