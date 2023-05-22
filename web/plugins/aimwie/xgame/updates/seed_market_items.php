<?php namespace Aimwie\Xgame\Updates;

use Seeder;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\UserX;
use Aimwie\Xgame\Models\MarketItem;

class SeedMarketItems extends Seeder
{
    public function run()
    {
        $items = Item::all();
        echo "Seed market items...\n";
        foreach ($items as $item) {

            $itemCount = rand(0, 14);

            echo "Seed market item: [x".$itemCount."] ".$item->title."\n";


            for ($i=0; $i < $itemCount; $i++) { 
                $marketItem = new MarketItem();
    
                $marketItem->user_id = rand(2,10);
                $marketItem->item_id = $item->id;
    
                $marketItem->price_coins = round($item->price_coins * (rand(10 , 50)/ 10));
                $marketItem->price_gems = 0;
                
                $marketItem->save();
            }
        

        }
        echo "Seed market items... DONE\n";
    }
}