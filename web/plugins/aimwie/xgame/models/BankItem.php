<?php namespace Aimwie\Xgame\Models;

use Model;

/**
 * Model
 */
class BankItem extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aimwie_xgame_bank_items';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'item' => [\Aimwie\Xgame\Models\Item::class],
        'user' => [\Aimwie\Xgame\Models\UserX::class]
    ];

    public function calcDays()
    {
        $now = time(); // or your date as well
        $createdAt = strtotime($this->created_at);
        $datediff = $now - $createdAt;

        return round($datediff / (60 * 60 * 24));
    }

    public function calcPayback()
    {
        $pledgePaybackBase = round($this->item->price_coins * 0.8);
        $pledgePaybackDay = round($this->item->price_coins * 0.01);
        $pledgeDays = $this->calcDays();
        $pledgePaybackTotal = $pledgePaybackBase + ($pledgePaybackDay * $pledgeDays);
        
        return $pledgePaybackTotal;
    }
}
