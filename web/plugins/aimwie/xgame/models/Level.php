<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class Level extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_levels';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
