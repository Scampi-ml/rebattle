<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class FightArchive extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_fights_archive';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'user' => [\Aimwie\Xgame\Models\UserX::class],
        'user2' => [\Aimwie\Xgame\Models\UserX::class],
        'opponent' => [\Aimwie\Xgame\Models\Opponent::class],
    ];

    public $hasMany = [
        'rounds' => [\Aimwie\Xgame\Models\FightRoundArchive::class],
    ];

}
