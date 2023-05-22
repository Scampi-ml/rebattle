<?php namespace Aimwie\Xgame\Updates;

use Seeder;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Craft;
use Aimwie\Xgame\Models\CraftItem;


class SeedCrafts extends Seeder
{
    public function run()
    {
        $this->seedCrafts();
    }
    public function seedCrafts()
    {
        echo "Seed Crafts... \n";
        $userLocations = UserItem::$userLocations;

        $craftMaterials = Item::select('id', 'title', 'type_id')
            ->whereNotIn('type_id', $userLocations)
            ->get()
            ->keyby('id')
            ->toArray();

        $items = Item::whereIn('type_id', $userLocations)->get();
        if ($items->count()) {
            foreach ($items as $item) {
                echo "Seed Craft for ".$item->title." \n";

                // Craft Item
                $craft = new Craft;
                $craft->item_id = $item->id;
                $craft->items_count = 0;
                $craft->level = 0;
                $craft->coins = 0;
                $craft->gems = 0;
                $craft->success_rate = rand(90, 100);
                $craft->save();

                // Add Craft Items (materials)
                for ($i=1; $i <= 2; $i++) { 
                    $material = $craftMaterials[array_rand($craftMaterials)];
                    echo "Seed Craft material: ".$material['title']." \n";
                    $craftItem = new CraftItem;
                    $craftItem->craft_id = $craft->id;
                    $craftItem->item_id = $material['id'];
                    $craftItem->save();
                }
            }
        }
    }
}