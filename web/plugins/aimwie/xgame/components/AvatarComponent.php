<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;

use Aimwie\Xgame\Models\Avatar;
use Auth;
use Flash;
use Redirect;


class AvatarComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AvatarComponent Component',
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

        $avatars = Avatar::where('is_public', 1)->get();
        
        $this->page['avatars'] = $avatars;
    }

    public function onAction()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        $avatar = Avatar::where('id', post('avatar_id'))
            ->where('is_public', 1)
            ->first();

        if (!$avatar) {
            Flash::error("Avatar not found!");
            Redirect::to("/avatar");
        }

        if ($avatar->coins > $user->coins) {
            Flash::warning("You don't have enough coins!");
            return Redirect::to('/avatar');
        }
        if ($avatar->gems > $user->gems) {
            Flash::warning("You don't have enough gems!");
            return Redirect::to('/avatar');
        }
        if ($avatar->level > $user->level) {
            Flash::warning("You don't have level ".$avatar->level."!");
            return Redirect::to('/avatar');
        }

        if ($avatar->coins) {
            $user->coins -= $avatar->coins;
        }
        if ($avatar->gems) {
            $user->gems -= $avatar->gems;
        }

        $user->avatar_id = $avatar->id;
        $user->img_url = $avatar->img_url;
        $user->save();

        Flash::success("You changed your player avatar!");
        return Redirect::to('/avatar');

    }
}
