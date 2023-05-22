<?php namespace Aimwie\Xgame\Updates;

use Seeder;
use RainLab\User\Models\User;
use Aimwie\Xgame\Models\Item;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Avatar;
use Aimwie\Xgame\Models\Level;
use Aimwie\Xgame\Models\Action;
use Aimwie\Xgame\Models\Craft;
use Hash;

class SeedUsers extends Seeder
{
    public function run()
    {
        $this->seedDemoUser();
        $this->formatUsers();
        $this->seedDemoUserItems();
        $this->seedDemoUserCraftItems();
    }

    public function seedDemoUser()
    {
        if (User::find(1)) {
            return false;
        }

        $password = "demopass";
        $user = User::create([
            'name' => "Demo User",
            'email' => "demo@user.email",
            'password' => $password,
            // 'password' => Hash::make($password),
            'password_confirmation' => $password,
            // 'password_confirm' => Hash::make($password),
        ]);
        $user->is_activated = 1;
        $user->activated_at = date("Y-m-d H:i:s");
        $user->save();

        // Seed other users
        for ($i=0; $i < 50; $i++) { 
            $faker = \Faker\Factory::create();
            $password = "demopass";
            $user = User::create([
                'name' => $faker->name,
                'email' => "demo".$i."@user.email",
                'password' => $password,
                // 'password' => Hash::make($password),
                'password_confirmation' => $password,
                // 'password_confirm' => Hash::make($password),
            ]);
            $user->is_activated = 1;
            $user->activated_at = date("Y-m-d H:i:s");
            $user->save();
        }

    }
    
    public function formatUsers()
    {
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
        $users = User::all();
        if ($users->count()) {
            foreach ($users as $user) {
                $faker = \Faker\Factory::create();
                $user->player_name = $faker->word.rand(10,99);
                
                $levelId = $user->id == 1 ? 2 : rand(1,5);
                $level = Level::find($levelId);
                $avatar = Avatar::inRandomOrder()->first();
                $user->avatar_id = $avatar->id;
                $user->img_url = $avatar->img_url;
                $user->coins = rand(100,1000);
                $user->gems = rand(0, 200);
                $user->level = $levelId;
                $user->add_points = 0;
                $user->xp = 0;
                $user->xp_max = $level->xp;
                
                $user->hp = config('re.stats.hp_base') + (config('re.stats.hp_increase') * $levelId);
                $user->hp_max = $user->hp;
                
                $user->hp_ts = null;
                
                $user->ap = config('re.stats.ap_base') + (config('re.stats.ap_increase') * $levelId);
                $user->ap_max = $user->ap;
                $user->ap_ts = null;
                
                $user->fight_id = null;
                $user->is_pvp = $user->id > 1 ? rand(0, 1) : 0;
                
                $user->power = config('re.stats.power_base') + (config('re.stats.power_increase') * $levelId);
                $user->defense = config('re.stats.defense_base') + (config('re.stats.defense_increase') * $levelId);
                $user->critical = 0;
                $user->storage = 0;
                $user->storage_max = 40;    
                
                // $user->actions_cache = json_encode($actions); // V1 test
                $user->actions_cache = json_encode([]);
                $user->save();
            }
        }
    }

    public function seedDemoUserItems()
    {
        return true; // Disabled
        $items = Item::limit(30)->get();
        foreach ($items as $item) {
            $userItem = new UserItem;
            $userItem->user_id = 1;
            $userItem->item_id = $item->id;
            $userItem->location = "storage";
            $userItem->save();

            // Set item
            // if (isset($setItem[$item->id])) {
            //     $userItem->location = $setItem[$item->id];
            //     $userItem->save();
            // }
        }
    }
    public function seedDemoUserCraftItems()
    {
        // Disabled
        $crafts = Craft::where('id', '<', '6')->get(); // First 5
        foreach ($crafts as $craft) {
            foreach ($craft->items as $craftItem) {
                $userItem = new UserItem;
                $userItem->user_id = 1;
                $userItem->item_id = $craftItem->item_id;
                $userItem->location = "storage";
                $userItem->save();
            }
        }
    }
}