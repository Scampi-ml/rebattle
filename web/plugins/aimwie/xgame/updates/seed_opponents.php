<?php namespace Aimwie\Xgame\Updates;

use Seeder;
use Aimwie\Xgame\Models\Opponent;
use Aimwie\Xgame\Models\OpponentItem;
use Aimwie\Xgame\Models\OpponentReward;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Level;
use Aimwie\Xgame\Models\Avatar;
use Aimwie\Xgame\Models\Action;

class SeedOpponents extends Seeder
{
    public function run()
    {
        $this->seedOpponents();
        $this->seedOpponentItems();
        $this->seedOpponentRewards();
    }

    public function seedOpponents()
    {
        echo "Seed opponents...";

        $opponentsFile = file_get_contents(__DIR__.'/items/opponents.json');
        $opponentsData = json_decode($opponentsFile, JSON_INVALID_UTF8_IGNORE);
        $opponentTitles = $opponentsData['opponents'];
    
        $levels = Level::all();
        $maxLevel = $levels->count();
        $hpBase = 10;
        $apBase = 10;
        $powerBase = 3;
        $defenseBase = 3;

        $avatars = Avatar::all()->toArray();

        $actions = Action::select([
            'id',
            'title',
            'description',
            'icon',
            'type',
            'ap',
            'amount'
        ])
        ->get()
        ->keyBy('id')
        ->toArray();


        foreach ($levels as $level) {
            
            $title = $opponentTitles[array_rand($opponentTitles)];
            $levelId = $level->id;

            echo $title."\n";
            $minLevel = $level->id - 2;

            $opponent = new Opponent;
            $opponent->player_name = $title." soldier";
            $opponent->hp_max = config('re.stats.hp_base') + (config('re.stats.hp_increase') * $levelId);
            $opponent->ap_max = config('re.stats.ap_base') + (config('re.stats.ap_increase') * $levelId);
            $opponent->power = config('re.stats.power_base') + (config('re.stats.power_increase') * $levelId);
            $opponent->defense = config('re.stats.defense_base') + (config('re.stats.defense_increase') * $levelId);
            $opponent->critical = 0;
            $opponent->avatar_mode = "avatar";
            $avatar = Avatar::find($levelId);
            if (!$avatar) { $avatar = Avatar::inRandomOrder()->first(); }
            $opponent->avatar_id = $avatar->id;
            $opponent->img_url = $avatar->img_url;
            $opponent->level = $level->id;
            $opponent->min_level = $minLevel < 1 ? 0 : $minLevel;
            $opponent->is_public = 1;
            $opponent->reward_mode = "win";
            $opponent->reward_size = 5;
            $opponent->save();

            $minLevel = $level->id - 1;
            $opponent = new Opponent;
            $opponent->player_name = $title." general";
            $opponent->hp_max = config('re.stats.hp_base') + (config('re.stats.hp_increase') * $levelId);
            $opponent->ap_max = config('re.stats.ap_base') + (config('re.stats.ap_increase') * $levelId);
            $opponent->power = config('re.stats.power_base') + (config('re.stats.power_increase') * $levelId);
            $opponent->defense = config('re.stats.defense_base') + (config('re.stats.defense_increase') * $levelId);
            $opponent->critical = 0;
            $opponent->avatar_mode = "avatar";
            $avatar = Avatar::find($levelId);
            if (!$avatar) { $avatar = Avatar::inRandomOrder()->first(); }
            $opponent->avatar_id = $avatar->id;
            $opponent->img_url = $avatar->img_url;
            $opponent->level = $level->id;
            $opponent->min_level = $minLevel < 1 ? 0 : $minLevel;
            $opponent->is_public = 1;
            $opponent->actions_cache = $actions;
            $opponent->reward_mode = "win";
            $opponent->reward_size = 5;
            $opponent->save();
        }
    }

    public function seedOpponentItems()
    {
        $userLocations = UserItem::$userLocations;
        echo "seedOpponentItems...\n";


        $opponents = Opponent::all();
        if ($opponents->count()) {
            $skipItems = true;
            foreach ($opponents as $opponent) {
                if ($skipItems) {
                    $skipItems = false;
                    continue;
                } else {
                    $skipItems = true;
                }
                foreach ($userLocations as $userLocation) {
                    
                    $item = Item::where([
                            'type_id' => $userLocation,
                            'level' => $opponent->level
                        ])->first();

                    if ($item) {
                        echo "Opponent: ".$opponent->title." | OpponentItem: ".$userLocation." / ".$item->title."\n";
                        $opponentItem = new OpponentItem;
                        $opponentItem->opponent_id = $opponent->id;
                        $opponentItem->item_id = $item->id;
                        $opponentItem->save();
                    }
                    
                }
            }
        }
    }

    public function seedOpponentRewards()
    {
        echo "seedOpponentRewards...\n";

        $userLocations = UserItem::$userLocations;
        $craftMaterials = Item::select('id', 'title', 'type_id')
            ->whereNotIn('type_id', $userLocations)
            ->get()
            ->keyby('id')
            ->toArray();

        $opponents = Opponent::all();
        $opponentsCount = $opponents->count();
        if ($opponentsCount) {
            $i = 0;
            foreach ($opponents as $opponent) {
                $i++;

                    // Coins
                    $opponentReward = new OpponentReward;
                    $opponentReward->opponent_id = $opponent->id;
                    $opponentReward->reward_type = "coins";
                    $opponentReward->item_id = null;
                    $opponentReward->success_rate = rand(90,100);
                    $opponentReward->quantity = rand(50, 80) * $i;
                    $opponentReward->save();

                    // Gems
                    $opponentReward = new OpponentReward;
                    $opponentReward->opponent_id = $opponent->id;
                    $opponentReward->reward_type = "gems";
                    $opponentReward->item_id = null;
                    $opponentReward->success_rate = $i;
                    $opponentReward->quantity = rand(1, 3);
                    $opponentReward->save();
                
                    // Items
                    for ($i=1; $i <= 10; $i++) { 
                        $material = $craftMaterials[array_rand($craftMaterials)];

                        $opponentReward = new OpponentReward;
                        $opponentReward->opponent_id = $opponent->id;
                        $opponentReward->reward_type = "item";
                        $opponentReward->item_id = $material['id'];
                        $opponentReward->success_rate = rand(90,100);
                        $opponentReward->quantity = 1;
                        $opponentReward->save();
                        echo "Opponent: ".$opponent->title." | OpponentReward: ".$material['title']." (SR:".$opponentReward->success_rate."%)\n";
                    }
            }
        }
    }
}