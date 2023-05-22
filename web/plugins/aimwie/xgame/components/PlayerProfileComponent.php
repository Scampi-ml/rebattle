<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserX;
use Aimwie\Xgame\Models\UserItem;
use Flash;
use Redirect;
use Auth;

class PlayerProfileComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'playerProfileComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $playerId = $this->param('playerId', null);

        $opponent = UserX::where('player_name', $playerId)->first();
        if (!$opponent) {
            Flash::warning("Player not found!");
            return Redirect::to('/404');
        }

        $userItems = UserItem::where('user_id', $opponent->id)
            ->where('location', 'storage')
            ->orderby('updated_at', 'asc')
            ->get();

        $this->page['opponent'] = $opponent;
        $this->page['userItems'] = $userItems;
        $this->addJs('/themes/x/assets/x/js/playerprofile.js?ts=');

    }
}
