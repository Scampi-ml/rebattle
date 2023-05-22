<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class Item extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_items';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $attachOne = [
        'img' => 'System\Models\File'
    ];

    public $jsonable = ['actions_cache'];

    public $hasMany = [
        'actions' => [\Aimwie\Xgame\Models\ItemAction::class]
    ];

    public function beforeCreate()
    {
        $this->actions_cache = [];
    }

    public function actionsCacheBuild()
    {
        $actionCache = [];
        $itemActions = $this->actions;
        if ($itemActions->count()) {
            foreach ($itemActions as $itemAction) {
                $actionCache[$itemAction->action_id] = [
                    'id' => $itemAction->action->id,
                    'title' => $itemAction->action->title,
                    'description' => $itemAction->action->description,
                    'icon' => $itemAction->action->icon,
                    'type' => $itemAction->action->type,
                    'ap' => $itemAction->action->ap,
                    'amount' => $itemAction->action->amount,
                ];
            }
        }
        $this->actions_cache = $actionCache;
    }

    public function afterSave()
    {
        if ($this->img) {
            $thumbUrl = str_replace(config('app.url'), "", $this->img->getThumb(100, 100, ['mode' => 'crop']));
        } else {
            $thumbUrl = "";
        }
        if ($this->img_url !== $thumbUrl) {
            $this->img_url = $thumbUrl;
            $this->save();
        }
    }
}
