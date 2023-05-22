<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class Avatar extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_avatars';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $attachOne = [
        'img' => 'System\Models\File'
    ];

    public function afterSave()
    {
        if ($this->img) {
            $thumbUrl = str_replace(config('app.url'), "", $this->img->getThumb(100, 150, ['mode' => 'crop']));
        } else {
            $thumbUrl = "";
        }
        if ($this->img_url !== $thumbUrl) {
            $this->img_url = $thumbUrl;
            $this->save();
        }
    }
}
