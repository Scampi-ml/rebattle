<?php namespace Aimwie\Xgame\Controllers;

use Backend\Classes\Controller;
use Aimwie\Xgame\Models\OpponentItem;
use BackendMenu;
use Redirect;

class Opponents extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\RelationController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aimwie.Xgame', 'aimwie-xgame', 'aimwie-xgame-opponents');
    }

    public function onOpponentItemDelete()
    {
        $opponentItem = OpponentItem::find(post('id'));
        if ($opponentItem) {
            $opponentItem->delete();
            return Redirect::to('/'.config('cms.backendUri').'/aimwie/xgame/opponents/update/'.$opponentItem->opponent_id.'?ts='.time().'#primarytab-items');
        }
    }
}
