<?php namespace Aimwie\Xgame\Updates;

use Seeder;
use Aimwie\Xgame\Models\Avatar;


class SeedAvatars extends Seeder
{
    public function run()
    {
        $gemFile = file_get_contents(__DIR__.'/items/gems.json');
        $gemData = json_decode($gemFile, JSON_INVALID_UTF8_IGNORE);
        $gemsList = $gemData['gems'];

        for ($x = 1; $x <= 10; $x++) {
            echo "avatar: ".$x."\n";
            $randomWord = $gemsList[array_rand($gemsList, 1)];
            $level = $x - 6;

            $avatar = new Avatar;
            $avatar->title = $randomWord." warrior";
            $avatar->level =  $level < 0 ? 0 : $level;
            if ($x < 4) {
                $coins = 0;
                $gems = 0;
            }
            if ($x > 3 && $x < 6) {
                $coins = rand(0,200)*10;
                $gems = 0;
            }
            if ($x > 5) {
                $coins = 0;
                $gems = rand(1,5);
            }
            $avatar->coins = $coins;
            $avatar->gems = $gems;
            $avatar->is_public = 1;
            $avatar->save();

            $avatar->img = __DIR__.'/items/img/avatar/'.$x.".png";
            $avatar->save();
        }
    }
}