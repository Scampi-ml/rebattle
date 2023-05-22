<?php namespace Aimwie\Xgame\Models;

use Model;
use Aimwie\Xgame\Models\FightArchive;

/**
 * Model
 */
class Fight extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_fights';

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
        'rounds' => [\Aimwie\Xgame\Models\FightRound::class],
    ];

    public function archive()
    {
        $thisArr = $this->toArray();
        $fightArchive = new FightArchive;
        foreach ($thisArr as $tKey => $tVal) {
            if (in_array($tKey, ["opponent", "user", "user2", "rounds"])) { continue; }
            if ($tKey == "id") {
                $fightArchive->original_id = $tVal;
            } else {
                $fightArchive->{$tKey} = $tVal;
            }
        }
        $fightArchive->save();

        if ($this->rounds->count()) {
            foreach ($this->rounds as $round) {
                $round->archive();
            }
        }
        $this->delete();
    }

}
