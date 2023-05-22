<?php namespace Aimwie\Xgame\Models;

use Model;
use Aimwie\Xgame\Models\FightRoundArchive;

/**
 * Model
 */
class FightRound extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_fight_rounds';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'userAction' => [\Aimwie\Xgame\Models\Action::class],
        'opponentAction' => [\Aimwie\Xgame\Models\Action::class],
    ];

    public function archive()
    {
        $thisArr = $this->toArray();
        $fightRoundArchive = new FightRoundArchive;
        foreach ($thisArr as $tKey => $tVal) {
            if (in_array($tKey, ["userAction", "opponentAction"])) { continue; }
            if ($tKey == "id") {
                $fightRoundArchive->original_id = $tVal;
            } else {
                $fightRoundArchive->{$tKey} = $tVal;
            }
        }
        $fightRoundArchive->save();
        $this->delete();
    }
}
