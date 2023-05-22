<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class FightRoundArchive extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_fight_rounds_archive';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'userAction' => [\Aimwie\Xgame\Models\Action::class],
        'opponentAction' => [\Aimwie\Xgame\Models\Action::class],
    ];
}
