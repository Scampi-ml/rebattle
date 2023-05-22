<?php namespace Aimwie\Xgame\Updates;

use Seeder;
use Aimwie\Xgame\Models\Action;

class SeedActions extends Seeder
{
    public function run()
    {
        $actionFile = file_get_contents(__DIR__.'/items/actions.json');
        $actionData = json_decode($actionFile, JSON_INVALID_UTF8_IGNORE);
        $actions = $actionData;
        echo "Seed actions... \n";
        foreach ($actions as $aData) {
            echo "Action: ".$aData['title']." / ".$aData['type']." \n";
            $action = new Action;
            $action->title = $aData['title'];
            $action->description = $aData['description'];
            $action->icon = $aData['icon'];
            $action->type = $aData['type'];
            $action->ap = $aData['ap'];
            $action->amount = $aData['amount'];
            $action->save();
        }
        echo "Seed actions... [DONE]\n";
    }
}