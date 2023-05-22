<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserX;
use Input;

class ScoreboardComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'ScoreboardComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $topId = Input::get('top');
        $orderByList = [
            "level" => [
                "title" => "Level",
                "icon" => "fas fa-star text-warning animate__animated animate__pulse animate__infinite"
            ],
            "win" => [
                "title" => "Win",
                "icon" => "fas fa-medal text-warning animate__animated animate__pulse animate__infinite"
            ],
            "lose" => [
                "title" => "Lose",
                "icon" => "far fa-dizzy text-warning animate__animated animate__pulse animate__infinite"
            ],
            "standoff" => [
                "title" => "Standoff",
                "icon" => "fas fa-balance-scale text-warning animate__animated animate__pulse animate__infinite"
            ],
            "power" => [
                "title" => "Power",
                "icon" => "fas fa-bolt text-warning animate__animated animate__pulse animate__infinite"
            ],
            "defense" => [
                "title" => "Defense",
                "icon" => "fas fa-shield-alt text-warning animate__animated animate__pulse animate__infinite"
            ],
            "hp" => [
                "title" => "HP",
                "icon" => "fas fa-heart text-warning animate__animated animate__pulse animate__infinite"
            ],
            "ap" => [
                "title" => "AP",
                "icon" => "fas fa-theater-masks text-warning animate__animated animate__pulse animate__infinite"
            ],
            "coins" => [
                "title" => "Coins",
                "icon" => "fas fa-coins text-warning animate__animated animate__pulse animate__infinite"
            ]
        ];
        if (!isset($orderByList[$topId])) {
            $topId = "level";
        }
        $top = $orderByList[$topId];

        $xUsers = UserX::select('*')
            ->orderby($topId, 'desc')
            ->limit(30)
            ->get();


        $this->page['xUsers'] = $xUsers; 
        $this->page['top'] = $top; 
        $this->page['topId'] = $topId; 
    }
}
