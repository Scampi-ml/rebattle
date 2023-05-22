<?php
namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Flash;
use Cache;
use Carbon\Carbon;


class DailySpinComponent extends ComponentBase
{


    public function componentDetails()
    {
        return [
            'name' => 'DailySpinComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    function onRun()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $usedToday = Cache::get('usedToday_' . $user->id, false);

        // Pass the value to the page
        $this->page['canSpin'] = !$usedToday;

        // Add CSS and JS files (if canSpin)
        if ($this->page['canSpin']) {
            $this->addCss('assets/css/wheel.css');
            $this->addJs('https://d3js.org/d3.v3.min.js');
            $this->addJs('assets/js/wheel.js?x=20230116-5');
        }
    }

    public function onGetGift()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $usedToday = Cache::get('usedToday_' . $user->id, false);

        if ($usedToday) {
            return [
                '#daily_spin_result' => $this->renderPartial(
                    '@dailyspincomponent-result.htm',
                    [
                        'message' => "You already got your gift today!",

                    ]
                )
            ];
        }

        $gotCoins = post('value');
        // MAX 100
        if ($gotCoins > 100) {
            $gotCoins = 100;
            // MIN 0
        } elseif ($gotCoins < 0) {
            $gotCoins = 0;
        }
        $coins = $gotCoins;

        $user->coins += $coins;
        $user->save();
        $currentTime = Carbon::now();
        $tillMidnight = Carbon::tomorrow()->startOfDay()->diffInSeconds($currentTime);


        $this->page['tillMidnight'] = $tillMidnight;


        Cache::put('usedToday_' . $user->id, true, $tillMidnight);

        return [
            '#daily_spin_result' => $this->renderPartial(
                '@dailyspincomponent-result.htm',
                [
                    'message' => "You got $coins coins!",
                ]
            ),
            '#user-main-coins' => $user->coins
        ];
    }
}