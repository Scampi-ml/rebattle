<?php namespace Aimwie\Xgame;

use System\Classes\PluginBase;
use RainLab\User\Controllers\Users as UsersController;
use RainLab\User\Models\User as UserModel;
use Event;
use Yaml;
use File;

class Plugin extends PluginBase
{
    public static $versionId = 4;
    public static $versionName = "1.0.3";
    public $require = ['RainLab.User'];

    public function boot()
    {
        $this->extendUsersRegister();
        $this->extendUsersController();
    }
    public function registerComponents()
    {
        return [
            \Aimwie\Xgame\Components\MainComponent::class => 'mainCom',
            \Aimwie\Xgame\Components\CityComponent::class => 'cityCom',
            \Aimwie\Xgame\Components\CraftComponent::class => 'craftCom',
            \Aimwie\Xgame\Components\InventoryComponent::class => 'inventoryCom',
            \Aimwie\Xgame\Components\ArenaComponent::class => 'arenaCom',
            \Aimwie\Xgame\Components\FightComponent::class => 'fightCom',
            \Aimwie\Xgame\Components\MarketComponent::class => 'marketCom',
            \Aimwie\Xgame\Components\BankComponent::class => 'bankCom',
            \Aimwie\Xgame\Components\ScoreboardComponent::class => 'scoreboardCom',
            \Aimwie\Xgame\Components\PlayerDashboardComponent::class => 'playerDashboardCom',
            \Aimwie\Xgame\Components\PlayerProfileComponent::class => 'playerProfileCom',
            \Aimwie\Xgame\Components\AvatarComponent::class => 'avatarCom',
            \Aimwie\Xgame\Components\StripeWebhookComponent::class => 'stripeWebhookCom',
            \Aimwie\Xgame\Components\DailySpinComponent::class => 'dailySpinCom',
        ];
    }

    public function registerSettings()
    {
    }

    protected function extendUsersRegister()
    {
        Event::listen('rainlab.user.register', function($user) {
            $user->player_name = "player".rand(111,999).$user->id;
            $user->level = 1;
            $user->hp_max = config('re.stats.hp_base') + config('re.stats.hp_increase');
            $user->hp = $user->hp_max;
            $user->ap = config('re.stats.ap_base') + config('re.stats.ap_increase');
            $user->ap_max = $user->ap;
            $user->xp = 0;
            $user->xp_max = config('re.xp_start');
            $user->power = config('re.stats.power_base') + config('re.stats.power_increase');
            $user->defense = config('re.stats.defense_base') + config('re.stats.defense_increase');
            $user->critical = 0;
            $user->storage = 0;
            $user->storage_max = config('re.stats.storage_base');
            $user->img_url = config('re.user_img_url');
            $user->save();
        });
    }
    protected function extendUsersController()
    {
        UsersController::extendFormFields(function($widget) {
            // Prevent extending of related form instead of the intended User form
            if (!$widget->model instanceof UserModel) {
                return;
            }

            if ($widget->isNested) {
                return;
            }

            $configFile = plugins_path('aimwie/xgame/config/player_fields.yaml');
            $config = Yaml::parse(File::get($configFile));
            $widget->addTabFields($config);
        });
    }
}
