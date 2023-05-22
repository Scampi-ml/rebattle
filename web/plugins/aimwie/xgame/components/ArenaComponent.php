<?php namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\Fight;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Opponent;
use Aimwie\Xgame\Models\UserX;
use Auth;
use Flash;
use Redirect;
use Cache;


class ArenaComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'FightComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun() {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        if ($user->fight_id) {
            return Redirect::to('/fight');
        }

        $section = $this->param('section', 'empire');

        if ($section == "pvp") {
            $opponents = UserX::where('is_pvp', 1)
                ->orderby('updated_at', 'desc')
                ->paginate(12); // TODO Select only needed columns.
        } else {
            $opponents = Opponent::where('is_public', true)
                ->paginate(12);
        }
        
        $this->page['opponents'] = $opponents;
        $this->page['section'] = $section;
        $this->addJs('/themes/x/assets/x/js/arena.js?ts=');
    }

    public function onStartPvp()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        if ($user->hp_ts) {
            Flash::warning("You must wiat till all HP renews.");
            return Redirect::to('/user');
        }

        if ($user->fight_id || $user->is_pvp) {
            Flash::warning("You already are in a fight!");
            return Redirect::to('/arena');
        }

        $user->is_pvp = true;
        $user->fight_id = null;
        $user->save();
        return Redirect::to('/arena');
    }

    public function onCancelPvp()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        if ($user->is_pvp) {
            $user->is_pvp = 0;
            $user->save();
        }

        return Redirect::to('/arena');
    }

    public function onCheckPvp()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }
        return [
            "fight_id" => $user->fight_id
        ];
    }

    public function onStartFight()
    {
        $user = Auth::getUser();
        if (!$user) {
            return Redirect::to('/user');
        }

        if ($user->hp_ts) {
            Flash::warning("You must wiat till all HP renews.");
            return Redirect::to('/user');
        }

        if ($user->fight_id || $user->is_pvp) {
            Flash::warning("You already are in a fight!");
            return Redirect::to('/arena');
        }

        // PVP
        if (post('user_id')) {
            $opponent = UserX::find(post('user_id'));
            if (!$opponent) {
                Flash::warning("Opponent not found!");
                return Redirect::to('/arena');
            }
            if (!$opponent->is_pvp || $opponent->fight_id) {
                Flash::warning("Opponent is in the game or canceld PVP invitation!");
                return Redirect::to('/arena');
            }
            if ($opponent->level > $user->level) {
                Flash::warning("Opponent is in another level!");
                return Redirect::to('/arena');
            }

            $fight = new Fight;
            $fight->is_pvp = 1;
            $fight->user_id = $user->id;
            $fight->user2_id = $opponent->id;
            $fight->opponent_hp = $opponent->hp_max;
            $fight->opponent_ap = $opponent->ap_max;
            $fight->round = 1;
            $fight->save();

            $opponent->fight_id = $fight->id;
            $opponent->hp_ts = null;
            $opponent->ap_ts = null;
            $opponent->is_pvp = 0;
            $opponent->save();
        // Opponent mode
        } else {
            $opponent = Opponent::find(post('opponent_id'));
            if (!$opponent) {
                return false;
            }
            if (!$opponent->is_public) {
                return false;
            }
            if ($opponent->min_level > $user->level) {
                return false;
            }

            $fight = new Fight;
            $fight->user_id = $user->id;
            $fight->opponent_id = $opponent->id;
            $fight->opponent_hp = $opponent->hp_max;
            $fight->opponent_ap = $opponent->ap_max;
            $fight->round = 1;
            $fight->save();
        }

        // Disable Renew logic
        $user->fight_id = $fight->id;
        $user->hp_ts = null;
        $user->ap_ts = null;
        $user->save();

        return Redirect::to('/fight');
    }
}
