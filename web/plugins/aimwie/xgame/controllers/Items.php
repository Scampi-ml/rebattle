<?php namespace Aimwie\Xgame\Controllers;

use Backend\Classes\Controller;
use Aimwie\Xgame\Models\ItemAction;
use BackendMenu;
use Redirect;

class Items extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'    ,'Backend\Behaviors\RelationController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aimwie.Xgame', 'aimwie-xgame', 'aimwie-xgame-items');
    }

    public function onItemActionDelete()
    {
        $itemAction = ItemAction::find(post('id'));
        if ($itemAction) {
            $itemAction->delete();
            return Redirect::to('/'.config('cms.backendUri').'/aimwie/xgame/items/update/'.$itemAction->item_id.'?ts='.time().'#primarytab-actions');
        }
    }
}
