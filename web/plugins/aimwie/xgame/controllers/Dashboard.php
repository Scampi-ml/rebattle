<?php namespace Aimwie\Xgame\Controllers;

use Aimwie\Xgame\Plugin;
use Backend\Classes\Controller;

use Aimwie\Xgame\Models\Action;
use Aimwie\Xgame\Models\Avatar;
use Aimwie\Xgame\Models\BankItem;
use Aimwie\Xgame\Models\Craft;
use Aimwie\Xgame\Models\FightArchive;
use Aimwie\Xgame\Models\FightRoundArchive;
use Aimwie\Xgame\Models\Level;
use Aimwie\Xgame\Models\MarketItem;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Item;

use BackendMenu;
use Request;
use Redirect;
use Cache;

class Dashboard extends Controller
{

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aimwie.Xgame', 'aimwie-xgame', 'aimwie-xgame-dashboard');
    }

    public function index()
    {
        BackendMenu::setContext('Aimwie.Xgame', 'aimwie-xgame', 'aimwie-xgame-dashboard');

        $versionId = Plugin::$versionId;
        $versionName = Plugin::$versionName;
        $version = Cache::get('version_check', null);
        $versionChecked = null;

        if ($version === null) {
            try {
                $cacheTTL = 3600 * 3;
                $updateServer = "http://rb.codetool.net/xcloud/checkupdate";
                $host = Request::getHost();
                $checkVersion = file_get_contents($updateServer.'?h='.$host);
                $checkData = json_decode($checkVersion, true);
                if (isset($checkData['ok']) && $checkData['ok'] == true && isset($checkData['version'])) {
                    $version = $checkData['version'];
                }
                $version['checked'] = date("Y-m-d H:i:s");
                Cache::put('version_check', $version, $cacheTTL);

            } catch (\Throwable $th) {
                // Nothing skip as it is...
            }
        }

        $data = [
            'actions' => Action::count(),
            'avatars' => Avatar::count(),
            'bank_items' => BankItem::count(),
            'crafts' => Craft::count(),
            'fights' => FightArchive::count(),
            'fight_rounds' => FightRoundArchive::count(),
            'levels' => Level::count(),
            'market_items' => MarketItem::count(),
            'user_items' => UserItem::count(),
            'items' => Item::count(),
        ];


        $this->vars['versionId'] = $versionId;
        $this->vars['versionName'] = $versionName;
        $this->vars['version'] = $version;
        $this->vars['versionChecked'] = isset($version['checked']) ? $version['checked'] : null;
        $this->vars['data'] = $data;
        $this->pageTitle = "Dashboard";




    }
}
