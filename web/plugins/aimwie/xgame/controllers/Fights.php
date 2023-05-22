<?php namespace Aimwie\Xgame\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Fights extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aimwie.Xgame', 'aimwie-xgame', 'aimwie-xgame-fights');
    }
}
