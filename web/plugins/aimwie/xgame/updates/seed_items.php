<?php namespace Aimwie\Xgame\Updates;

use Seeder;
use Aimwie\Xgame\Models\Level;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\ItemAction;
use Aimwie\Xgame\Models\Action;
use Storage;

class SeedItems extends Seeder
{
    // old one
    public static $titles = [
        "Stone",
        "Steel",
        "Iron",
        "Copper",
        "Brass",
        "Bronze",
        "Titanium",
        "Fire",
        "Tiger",
        "Lion",
        "Sapphire",
        "Emerald",
        "Dimond",
        "Ruby",
        "Royal"
    ];

    public function run()
    {
        $this->seedWearables();
        $this->seedCraftBase();
    }

    public function seedWearables()
    {
        $levels = Level::all();
        $userLocations = UserItem::$userLocations;
        $levelCount = 0;
        $fileList = [];
        $priceBaseCoins = 1000;
        $priceBaseSell = 150;

        $gemFile = file_get_contents(__DIR__.'/items/gems.json');
        $gemData = json_decode($gemFile, JSON_INVALID_UTF8_IGNORE);
        $gems = $gemData['gems'];

        $actionMin = 0;
        $actionMax = 2;
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

        foreach ($userLocations as $location) {
            $files = array_diff(scandir(__DIR__.'/items/img/'.$location.'/'), [".", "..", ".DS_Store", ".gitignore"]);
            $fileList[$location] = $files;
        }

        foreach ($levels as $level) {
            $randomWordId = array_rand($gems);
            $randomWord = $gems[$randomWordId];
            unset($gems[$randomWordId]);
            
            foreach ($userLocations as $location) {
                $levelId = $level->id;
                $title = $randomWord." ".($location == "weapon" ? "sword" : $location);
                echo "Item: ".$title ."\n";
                $item = new Item;
                $item->title = $title;
                $item->price_coins= ($levelId)*$priceBaseCoins;
                $item->price_gems = 0;
                $item->type_id = $location;
                $item->level = $levelId;
                $item->hp = 0;
                $item->ap = 0;
                $powerValue = config('re.stats.weapon_base') + (config('re.stats.weapon_increase') * $levelId);
                $item->power = $location == "weapon" ? $powerValue : 0;
                $defenseValue = config('re.stats.defense_item_base') + (config('re.stats.defense_item_increase') * $levelId);
                $item->defense = !in_array($location, ["weapon", "bag"]) ? $defenseValue : 0;
                $item->critical = $location == "bag" ? 0 : rand(0,2);
                $item->storage_max = $location == "bag" ? 8 * $levelId : 0;
                $item->save();

                if (($levelId) > count($fileList[$location])) {
                    $fileId = array_rand($fileList[$location]);
                } else {
                    $aKeys = array_keys($fileList[$location]);
                    $fileId = $aKeys[$levelCount];
                }

                $imgFile = $fileList[$location][$fileId];
                $item->img = __DIR__.'/items/img/'.$location.'/'.$imgFile;
                $item->save();

                if ($location != "bag") {
                    // Seed Actions
                    $actionSize = rand($actionMin, $actionMax);
                    if ($actionSize) {
                        for ($i=0; $i < $actionSize; $i++) { 
                            $itemAction = new ItemAction;   
                            $itemAction->item_id = $item->id;  
                            $itemAction->action_id = array_rand($actions);
                            $itemAction->save();
                        }
                    }
                }

                // Variations
                if (in_array($location, ["armor", "boots", "helmet", "pants", "shield", "weapon"])) {
                    $this->seedWearableVariations($item, $fileId, $actions, $gems);
                }
            }
            $levelCount++;
        }

    }

    public function seedWearableVariations($baseItem, $fileId, $actions, $gems)
    {
        $colors = ["Blue", "Purple", "Green", "Yellow", "Red"];
        $colorsTitleRemap = ["Blue"=> "+", "Purple" => "++", "Green" => "hardened", "Yellow" => "golden", "Red" => "royal"];
        $itemRemap = [1=>4,2=>3,3=>5,4=>1,5=>2];
        $itemId = $baseItem->level+1;
        $itemId = $itemId > 5 ? rand(1,5) : $itemId;
        $imgFile = $itemRemap[$itemId].".png";

        $randomWord = $gems[array_rand($gems)];
        $titleBase = $randomWord." ".($baseItem->type_id == "weapon" ? "sword" : $baseItem->type_id);


        foreach ($colors as $colorIndex => $color) {
            // $files = array_diff(scandir(__DIR__.'/items/img/_op/'.$baseItem->type_id.'/'.$color.'/'), [".", "..", ".DS_Store", ".gitignore"]);
            // $files = array_values($files);
            
            $item = $baseItem->replicate();
            $item->title = $titleBase." ".$colorsTitleRemap[$color]; // TODO Color name => Spec name
            echo "Variation: ".$item->title ."\n";

            $item->price_coins= round($item->price_coins * (1+((1+$colorIndex)/5)));

            $item->critical = 2 + $colorIndex;
            
            $item->save();

            $actionSize = rand(2, 4);
            if ($actionSize) {
                for ($i=0; $i < $actionSize; $i++) { 
                    $itemAction = new ItemAction;   
                    $itemAction->item_id = $item->id;  
                    $itemAction->action_id = array_rand($actions);
                    $itemAction->save();
                }
            }

            // if ($baseItem->level > count($files)) {
            //     $fileId = array_rand($files);
            // } else {
            //     $aKeys = array_keys($files);
            //     $fileId = $aKeys[$baseItem->level];
            // }

            // $fileIdRemap = isset($itemRemap[$fileId]) ? $itemRemap[$fileId] : $fileId;

            // $imgFile = $files[$fileIdRemap];
            $item->img = __DIR__.'/items/img/_op/'.$baseItem->type_id.'/'.$color.'/'.$imgFile;
            $item->save();
        }

    }

    public function seedCraftBase()
    {
        $gemFile = file_get_contents(__DIR__.'/items/gems.json');
        $gemData = json_decode($gemFile, JSON_INVALID_UTF8_IGNORE);
        $gems = $gemData['gems'];

        $craftGroups =  array_diff(scandir(__DIR__.'/items/img/_craft/'), [".", "..", ".DS_Store", ".gitignore"]);

        foreach ($craftGroups as $craftGroup) {
            $craftGroupItems =  array_diff(scandir(__DIR__.'/items/img/_craft/'.$craftGroup.'/'), [".", "..", ".DS_Store", ".gitignore"]);
            if (is_array($craftGroupItems) && count($craftGroupItems)) {
                foreach ($craftGroupItems as $craftGroupItem) {
                    $randomWord = $gems[array_rand($gems)];
                    $title = ucfirst($craftGroup." of ".$randomWord);
                    echo "Item: ".$title ."\n";
                    $item = new Item;
                    $item->title = $title;
                    $item->price_coins= rand(100, 200);
                    $item->price_gems = 0;
                    $item->type_id = $craftGroup;
                    $item->level = 0;
                    $item->hp = 0;
                    $item->ap = 0;
                    $item->power = 0;
                    $item->defense = 0;
                    $item->critical = 0;
                    $item->storage_max = 0;
                    $item->save();

                    $item->img = __DIR__.'/items/img/_craft/'.$craftGroup.'/'.$craftGroupItem;
                    $item->save();
                }
            } else {
                echo "SKIP craftGroup: ".$craftGroup."\n";
            }
        }
    }

    public function seedOldFn()
    {
        $fileNames = [
            "sword.json",
            "shield.json",
        ];
        foreach ($fileNames as $fileName) {
            $file = file_get_contents(__DIR__.'/items/'.$fileName);
            try {
                $jsonData = json_decode($file, JSON_INVALID_UTF8_IGNORE);
            } catch (\Exception $e) {
                echo $e->getMessage()."\n";
                continue;
            }

            foreach ($jsonData as $data) {
                echo "Item: ".$data['title']."\n";
                $item = new Item;
                $item->title = $data['title'];
                $item->price_coins= $data['price_coins'];
                $item->price_gems = $data['price_gems'];
                $item->type_id = $data['type_id'];
                $item->level = $data['level'];
                $item->hp = $data['hp'];
                $item->ap = $data['ap'];
                $item->power = $data['power'];
                $item->defense = $data['defense'];
                $item->critical = $data['critical'];
                $item->storage_max = $data['storage_max'];
                $item->save();

                // Add Img
                if ($data['img']) {
                    $item->img = __DIR__.'/items/img/'.$data['img'];
                    $item->save();
                }

                // $item->img_url = str_replace(config('app.url'), "", $item->img->getThumb(100, 100, ['mode' => 'crop']));
                // $item->save();
            }
        }
    }
}