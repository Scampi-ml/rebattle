<?php namespace Aimwie\Xgame\Updates;

use Seeder;
use Aimwie\Xgame\Models\Level;


class SeedLevels extends Seeder
{
    public function run()
    {
        $maxLevel = config('re.max_level');
        $startxp = config('re.xp_start');
        $xpRatio = config('re.xp_ratio');
        $xpNow = $startxp;
        $points = config('re.add_points_base');
        $pointsRatio = config('re.add_points_ratio');
        
        for ($i=1; $i <= $maxLevel; $i++) {

            $level = new Level;
            $level->id = $i;
            $level->xp = $xpNow;
            $level->points = $points;
            $level->save();

            echo $level->id." : ".$xpNow."\n";
            $xpNow = round($xpNow  * $xpRatio);
            $points = round($points  * $pointsRatio);
        }
    }
}