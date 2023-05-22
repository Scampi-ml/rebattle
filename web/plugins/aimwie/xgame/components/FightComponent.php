<?php
namespace Aimwie\Xgame\Components;

use Cms\Classes\ComponentBase;
use Aimwie\Xgame\Models\UserX;
use Aimwie\Xgame\Models\Fight;
use Aimwie\Xgame\Models\FightArchive;
use Aimwie\Xgame\Models\FightRound;
use Aimwie\Xgame\Models\FightRoundArchive;
use Aimwie\Xgame\Models\UserItem;
use Aimwie\Xgame\Models\Opponent;
use Aimwie\Xgame\Models\OpponentReward;
use Aimwie\Xgame\Models\Action;
use Aimwie\Xgame\Models\Level;
use Auth;
use Flash;
use Redirect;
use Cache;
use Session;


class FightComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'FightComponent Component',
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
        $user = UserX::find($user->id);

        $fightEnded = false;
        $fightResume = null;
        $fightPvpWait = false;
        $fight = $user->fight_id ? Fight::find($user->fight_id) : null;
        if ($fight) {
            $fightRounds = FightRound::where('fight_id', $fight->id)
                ->orderby('id', 'desc')
                ->with(['userAction', 'opponentAction'])
                ->get();
            if ($fight->user_id != $user->id && $fight->user2_id != $user->id) {
                $user->fight_id = null;
                $user->save();
                $fight = null;
            }
        } else {
            $fightArchiveOriginalId = Cache::get('fight_archive_' . $user->id, null);
            if (!$fightArchiveOriginalId) {
                return Redirect::to('/arena');
            }
            $fightArchive = FightArchive::where('original_id', $fightArchiveOriginalId)->first();
            if (!$fightArchive) {
                Cache::forget('fight_archive_' . $user->id);
                Flash::warning('Fight not found!');
                return Redirect::to('/arena');
            }
            if ($fightArchive->user_id != $user->id && $fightArchive->user2_id != $user->id) {
                Cache::forget('fight_archive_' . $user->id);
                Flash::warning('Fight not found#2!');
                return Redirect::to('/arena');
            }
            $fight = $fightArchive;
            $fightRounds = FightRoundArchive::where('fight_id', $fightArchive->original_id)
                ->orderby('id', 'desc')
                ->with(['userAction', 'opponentAction'])
                ->get();
            $fightEnded = true;
            $fightResume = Cache::get('fight_resume_' . $user->id, null);
        }


        if ($fight->is_pvp) {
            $opponent = $fight->user_id != $user->id ? $fight->user : $fight->user2;
            $userPart = $fight->user_id == $user->id ? "user" : "user2";
            $opponentPart = $fight->user_id != $user->id ? "user" : "user2";
            if ($fight->{$userPart . "_attack_id"}) {
                $fightPvpWait = true;
            }
        } else {
            $opponent = $fight->opponent;
            $opponent->hp = $fight->opponent_hp;
            $opponent->ap = $fight->opponent_ap;
        }

        // BugFix: Ends fight if HP drops 0
        if (!$fightEnded && !$fight->is_pvp) {
            /* STANDOFF */
            if ($opponent->hp <= 0 && $user->hp <= 0) {
                $fight->winner = "standoff";
                return self::endFight($fight, $user, $opponent, []);
                /* WON */
            } elseif ($opponent->hp <= 0) {
                $fight->winner = "user";
                return self::endFight($fight, $user, $opponent, []);
                /* LOSE */
            } elseif ($user->hp <= 0) {
                $fight->winner = "opponent";
                return self::endFight($fight, $user, $opponent, []);
            }
        }

        $roundData = Cache::get('round_data_' . $user->id, null);
        if ($roundData) {
            Cache::forget('round_data_' . $user->id);
        }

        // UI
        $this->page['opponent'] = $opponent;
        $this->page['fight'] = $fight;
        $this->page['fightRounds'] = $fightRounds;
        $this->page['fightEnded'] = $fightEnded;
        $this->page['fightResume'] = $fightResume;
        $this->page['fightPvpWait'] = $fightPvpWait;
        $this->page['roundData'] = $roundData;

        $this->addJs('/themes/x/assets/x/js/fight.js?ts=');
    }

    public function onFight()
    {
        $user = Auth::getUser();
        if (!$user) {
            return false; // TODO: redirect
        }
        $userActions = json_decode($user->actions_cache, true);

        $fight = Fight::find($user->fight_id);
        if (!$fight) {
            return Redirect::to('/fight');
        }
        if ($fight->is_pvp) {
            $userPart = $fight->user_id == $user->id ? "user" : "user2";
            $opponentPart = $fight->user_id != $user->id ? "user" : "user2";
            $opponent = $fight->{$opponentPart};

            if (!$fight->{$userPart . "_attack_id"} && in_array(post('userAttackId'), [1, 2, 3])) {
                $fight->{$userPart . "_attack_id"} = post('userAttackId');
                $fight->{$userPart . "_block_id"} = post('userBlockId');
                $fight->{$userPart . "_action_id"} = post('userActionId') == "" ? null : post('userActionId');
                $fight->save();
            }

            $userAttackId = $fight->{$userPart . "_attack_id"};
            $userBlockId = $fight->{$userPart . "_block_id"};
            $userActionId = $fight->{$userPart . "_action_id"};

            $opponentAttackId = $fight->{$opponentPart . "_attack_id"};
            $opponentBlockId = $fight->{$opponentPart . "_block_id"};
            $opponentActionId = $fight->{$opponentPart . "_action_id"};

            if (!$userAttackId && !$opponentAttackId) {
                return Redirect::to('/fight');
            }
            if (!$opponentAttackId || $userPart == "user2") {
                return Redirect::to('/fight?wait=1');
            }
        } else {
            $opponent = $fight->opponent;
            $opponent->hp = $fight->opponent_hp;
            $opponent->ap = $fight->opponent_ap;

            $userAttackId = post('userAttackId');
            $userBlockId = post('userBlockId');
            $userActionId = post('userActionId') == "" ? null : post('userActionId');

            $opponentAttackId = rand(1, 3);
            $opponentBlockId = rand(1, 3);
            $opponentActionId = is_array($opponent->actions_cache) && count($opponent->actions_cache) ? array_rand($opponent->actions_cache) : null;
        }

        $userHit = 0;
        $opponentHit = 0;
        $criticalMin = 0;
        $criticalMax = 100; // Do we need it?

        /* USER Data */
        $userAction = null;
        $userDefenseBoost = 1; // Ratio (1 = regular)
        $userCritical = $user->critical > $criticalMax ? $criticalMax : $user->critical;
        $userCriticalOn = $userCritical < 1 ? false : (rand(1, 100) <= $userCritical ? true : false);
        $userPowerBoost = 1; // Ratio (1 = regular)


        // Action
        if ($userActionId && isset($userActions[$userActionId])) {
            $userAction = $userActions[$userActionId];
            if ($user->ap < $userAction['ap']) {
                $userActionId = null;
                $userAction = null;
            } else {
                $user->ap -= $userAction['ap'];
                $user->save();
            }
        }

        /* Opponent data */
        $opponentAction = null; // Pick random
        $opponentDefenseBoost = 1; // Ratio (1 = regular)
        $opponentPowerBoost = 1; // Ratio (1 = regular)
        $opponentCritical = $opponent->critical > $criticalMax ? $criticalMax : $opponent->critical;
        $opponentCriticalOn = $opponentCritical < 1 ? false : (rand(1, 100) <= $opponentCritical ? true : false);

        // Action
        // AUTO-PICK ACTON: END
        if ($opponentActionId && isset($opponent->actions_cache[$opponentActionId])) {
            $opponentAction = $opponent->actions_cache[$opponentActionId];
            if ($opponent->ap < $opponentAction['ap']) {
                $opponentActionId = null;
                $opponentAction = null;
            } else {
                if ($fight->is_pvp) {
                    $opponent->ap -= $opponentAction['ap'];
                    $opponent->save();
                } else {
                    $fight->opponent_ap -= $opponentAction['ap'];
                    $fight->save();
                }
            }
        }

        // == ACTION == [stop]
        $actionStop = false;
        if ($userAction && $userAction['type'] == "stop") {
            $actionStop = true;
        }
        if ($opponentAction && $opponentAction['type'] == "stop") {
            $actionStop = true;
        }

        if (!$actionStop) {
            // == ACTION == [power_procent]
            if ($userAction && $userAction['type'] == "power_procent") {
                $userPowerBoost = 1 + ($userAction['amount'] / 100);
            }
            if ($opponentAction && $opponentAction['type'] == "power_procent") {
                $opponentPowerBoost = 1 + ($opponentAction['amount'] / 100);
            }
            // == ACTION == [defense_procent]
            if ($userAction && $userAction['type'] == "defense_procent") {
                $userDefenseBoost = 1 + ($userAction['amount'] / 100);
            }
            if ($opponentAction && $opponentAction['type'] == "defense_procent") {
                $opponentDefenseBoost = 1 + ($opponentAction['amount'] / 100);
            }
            // == ACTION == [hp]
            if ($userAction && $userAction['type'] == "hp") {
                $user->hp += $userAction['amount'];
                if ($user->hp_max < $user->hp) {
                    $user->hp = $user->hp_max;
                }
            }
            if ($opponentAction && $opponentAction['type'] == "hp") {
                if ($fight->is_pvp) {
                    $opponent->hp += $opponentAction['amount'];
                    if ($opponent->hp_max < $opponent->hp) {
                        $opponent->hp = $opponent->hp_max;
                    }
                } else {
                    $fight->opponent_hp += $opponentAction['amount'];
                    if ($opponent->hp_max < $fight->opponent_hp) {
                        $fight->opponent_hp = $opponent->hp_max;
                    }
                }
            }
            // == ACTION == [random_block]
            if ($userAction && $userAction['type'] == "random_block") {
                $opponentBlockId = rand(1, 3);
            }
            if ($opponentAction && $opponentAction['type'] == "random_block") {
                $userBlockId = rand(1, 3);
            }
            // == ACTION == [random_attack]
            if ($userAction && $userAction['type'] == "random_attack") {
                $opponentAttackId = rand(1, 3);
            }
            if ($opponentAction && $opponentAction['type'] == "random_attack") {
                $userAttackId = rand(1, 3);
            }
            // == ACTION == [random_both]
            if ($userAction && $userAction['type'] == "random_both") {
                $opponentAttackId = rand(1, 3);
                $opponentBlockId = rand(1, 3);
            }
            if ($opponentAction && $opponentAction['type'] == "random_both") {
                $userAttackId = rand(1, 3);
                $userBlockId = rand(1, 3);
            }
        }

        // Power + Defenese
        $userDefense = round($user->defense * $userDefenseBoost);
        $userPower = round($user->power * $userPowerBoost);
        $userPower = $userCriticalOn ? $userPower * 2 : $userPower;
        $opponentDefense = round($opponent->defense * $opponentDefenseBoost);
        $opponentPower = round($opponent->power * $opponentPowerBoost);
        $opponentPower = $opponentCriticalOn ? $opponentPower * 2 : $opponentPower;
        // USER + Opponent hitPower
        $userHitPower = $userPower - $opponentDefense;
        $userHitPower = $userHitPower < 1 ? 1 : $userHitPower;
        $opponentHitPower = $opponentPower - $userDefense;
        $opponentHitPower = $opponentHitPower < 1 ? 1 : $opponentHitPower;

        // == ACTION == [ping_pong]
        if ((!$actionStop && $opponentAction && $opponentAction['type'] == 'ping_pong') || (!$actionStop && $userAction && $userAction['type'] == 'ping_pong')) {
            if ($userAttackId == $opponentAttackId) {
                $uhp = $userHitPower;
                $ohp = $opponentHitPower;
                $userHitPower = $ohp;
                $opponentHitPower = $uhp;
            }
        }

        // *** USER -> OPPONENT
        if ($userAttackId != $opponentBlockId) {
            $userHit = $userHitPower; // For flash msg
            if ($fight->is_pvp) {
                $opponent->hp -= $userHitPower;
            } else {
                $fight->opponent_hp -= $userHitPower;
            }
        }

        // == ACTION == [power_heal]
        if (!$actionStop && $userCriticalOn && $opponentAction && $opponentAction['type'] == 'power_heal') {
            if ($fight->is_pvp) {
                $opponent->hp += $opponent->power;
                if ($opponent->hp_max < $opponent->hp) {
                    $opponent->hp = $opponent->hp_max;
                }
            } else {
                $fight->opponent_hp += $opponent->power;
                if ($opponent->hp_max < $fight->opponent_hp) {
                    $fight->opponent_hp = $opponent->hp_max;
                }
            }
        }
        // == ACTION == [critical_back]
        if (!$actionStop && $userCriticalOn && $opponentAction && $opponentAction['type'] == 'critical_back') {
            $user->hp -= $userHitPower;
        }

        // *** OPPONENT -> USER
        if ($opponentAttackId != $userBlockId) {
            $opponentHit = $opponentHitPower; // For flash msg
            $user->hp -= $opponentHitPower;
            $user->save();
        }

        // == ACTION == [power_heal]
        if ($opponentCriticalOn && $userAction && $userAction['type'] == 'power_heal') {
            $user->hp += $user->power;
            if ($user->hp_max < $user->hp) {
                $user->hp = $user->hp_max;
            }
        }
        // == ACTION == [critical_back]
        if ($opponentCriticalOn && $userAction && $userAction['type'] == 'critical_back') {
            if ($fight->is_pvp) {
                $opponent->hp -= $opponentHitPower;
            } else {
                $fight->opponent_hp -= $opponentHitPower;
            }
        }


        $fightRound = new FightRound;
        $fightRound->fight_id = $fight->id;
        $fightRound->user_id = $user->id;
        if ($fight->is_pvp) {
            $fightRound->user2_id = $opponent->id;
            $fightRound->opponent_id = null;
            $fightRound->opponent_hp = $opponent->hp;
            $fightRound->opponent_ap = $opponent->ap;
        } else {
            $fightRound->user2_id = null;
            $fightRound->opponent_id = $opponent->id;
            $fightRound->opponent_hp = $fight->opponent_hp;
            $fightRound->opponent_ap = $fight->opponent_ap;
        }
        $fightRound->round = $fight->round;
        $fightRound->user_attack_id = $userAttackId;
        $fightRound->user_block_id = $userBlockId;
        $fightRound->user_action_id = $userActionId;
        $fightRound->opponent_attack_id = $opponentAttackId;
        $fightRound->opponent_block_id = $opponentBlockId;
        $fightRound->opponent_action_id = $opponentActionId;
        $fightRound->user_hp = $user->hp;
        $fightRound->user_ap = $user->ap;
        $fightRound->user_hit = $userHit;
        $fightRound->opponent_hit = $opponentHit;
        $fightRound->user_hit_critical = $userCriticalOn;
        $fightRound->opponent_hit_critical = $opponentCriticalOn;
        $fightRound->save();

        $fight->user_attack_id = null;
        $fight->user_block_id = null;
        $fight->user_action_id = null;

        $fight->user2_attack_id = null;
        $fight->user2_block_id = null;
        $fight->user2_action_id = null;

        $fight->round += 1;
        $fight->save();
        $user->save();
        if ($fight->is_pvp) {
            $opponent->save();
        }

        /* === SET ROUND DATA FOR DISPLAY === */

        $roundData = [
            "userHit" => $userHit,
            "userCriticalOn" => $userCriticalOn,
            "userActionId" => $userActionId,
            "opponentHit" => $opponentHit,
            "opponentCriticalOn" => $opponentCriticalOn,
            "opponentActionId" => $opponentActionId,
        ];
        Cache::put('round_data_' . $user->id, $roundData);
        if ($fight->is_pvp) {
            $roundData = [
                "userHit" => $opponentHit,
                "userCriticalOn" => $opponentCriticalOn,
                "userActionId" => $opponentActionId,
                "opponentHit" => $userHit,
                "opponentCriticalOn" => $userCriticalOn,
                "opponentActionId" => $userActionId,
            ];
            Cache::put('round_data_' . $opponent->id, $roundData);
        }

        /* === CHECK IF FIGHT HAS ENDED === */

        /* STANDOFF */
        if ($opponent->hp <= 0 && $user->hp <= 0) {
            $fight->winner = "standoff";
            return self::endFight($fight, $user, $opponent, []);
            /* WON */
        } elseif ($opponent->hp <= 0) {
            $fight->winner = "user";
            return self::endFight($fight, $user, $opponent, []);
            /* LOSE */
        } elseif ($user->hp <= 0) {
            $fight->winner = "opponent";
            return self::endFight($fight, $user, $opponent, []);
            // Continue fight
        } else {
            return Redirect::to('/fight');
        }
    }

    private static function endFight($fight, $user, $opponent, $data)
    {
        $xpWinRatio = config("re.xp_win_ratio");
        $xpStandoffRatio = config("re.xp_win_ratio");
        // WIN / STANDOFF / LOSE
        $userXp = 0;
        $opponentXp = 0;
        if ($fight->winner == "user") {
            $user->win += 1;
            $userXp = $user->xp += round($opponent->hp_max * $xpWinRatio);
            if ($fight->is_pvp) {
                $opponent->lose += 1;
            }
        } else if ($fight->winner == "standoff") {
            $user->standoff += 1;
            $userXp = $user->xp += round($opponent->hp_max * $xpStandoffRatio);
            if ($fight->is_pvp) {
                $opponent->standoff += 1;
                $opponentXp = $opponent->xp += round($user->hp_max * $xpStandoffRatio);
            }
        } else {
            $user->lose += 1;
            if ($fight->is_pvp) {
                $opponent->win += 1;
                $opponentXp = $opponent->xp += round($user->hp_max * $xpStandoffRatio);
            }
        }

        // New Level : USER
        if ($user->xp_max <= $user->xp) {
            $levelDiff = $user->xp - $user->xp_max;
            $user->xp = $levelDiff;
            $levelNow = Level::find($user->level);
            if ($levelNow) {
                $user->add_points = $levelNow->points;
                $levelNext = Level::find($user->level + 1);
                if ($levelNext) {
                    $user->level = $levelNext->id;
                    $user->xp_max = $levelNext->xp;
                }
            }
        }
        // New Level : OPPONENT
        if ($fight->is_pvp) {
            if ($opponent->xp_max <= $opponent->xp) {
                $levelDiff = $opponent->xp - $opponent->xp_max;
                $opponent->xp = $levelDiff;
                $levelNow = Level::find($opponent->level);
                if ($levelNow) {
                    $opponent->add_points = $levelNow->points;
                    $levelNext = Level::find($opponent->level + 1);
                    if ($levelNext) {
                        $opponent->level = $levelNext->id;
                        $opponent->xp_max = $levelNext->xp;
                    }
                }
            }
        }
        // HP
        if ($user->hp < $user->hp_max) {
            $user->hp = $user->hp < 0 ? 0 : $user->hp;
            $tsDiff = round(($user->hp_max - $user->hp) * config('re.hp_rate'));
            $user->hp_ts = time() + $tsDiff;
        }
        // AP
        if ($user->ap < $user->ap_max) {
            $user->ap = $user->ap < 0 ? 0 : $user->ap;
            $tsDiff = round(($user->ap_max - $user->ap) * config('re.ap_rate'));
            $user->ap_ts = time() + $tsDiff;
        }
        // End data
        $user->fight_id = null;
        $user->save();

        if ($fight->is_pvp) {
            if ($opponent->hp < $opponent->hp_max) {
                $opponent->hp = $opponent->hp < 0 ? 0 : $opponent->hp;
                $tsDiff = round(($opponent->hp_max - $opponent->hp) * config('re.hp_rate'));
                $opponent->hp_ts = time() + $tsDiff;
            }
            // AP
            if ($opponent->ap < $opponent->ap_max) {
                $opponent->ap = $opponent->ap < 0 ? 0 : $opponent->ap;
                $tsDiff = round(($opponent->ap_max - $opponent->ap) * config('re.ap_rate'));
                $opponent->ap_ts = time() + $tsDiff;
            }
            // End data
            $opponent->fight_id = null;
            $opponent->save();
        }

        $fight->archive(); // TODO: Archive Fight

        // TODO: If Win Get rewards
        // TODO: Put Resume in DB
        $rewardCoins = 0;
        $rewardGems = 0;
        $rewardList = [];
        if (!$fight->is_pvp) {
            if ($fight->winner == "user" || ($fight->winner == "standoff" && $opponent->reward_mode == "standoff")) {
                if ($opponent->reward_size && $opponent->rewards_sr_sum) {
                    $userStorage = $user->storage_max - $user->storage;
                    for ($i = 1; $i <= $opponent->reward_size; $i++) {
                        $randId = rand(0, $opponent->rewards_sr_sum);
                        $opponentReward = OpponentReward::where('opponent_id', $opponent->id)
                            ->where('success_rate_position', '>=', $randId)
                            ->orderby('success_rate_position', 'asc')
                            ->first();
                        $debug[] = ["randId" => $randId, "opponentReward" => $opponentReward];
                        if (!$opponentReward) {
                            continue;
                        }
                        if ($opponentReward->reward_type == "coins") {
                            $rewardCoins += $opponentReward->quantity < 1 ? 0 : $opponentReward->quantity;
                            $rewardList[] = ["type" => "coins", "quantity" => $opponentReward->quantity];
                        } elseif ($opponentReward->reward_type == "gems") {
                            $rewardGems += $opponentReward->quantity < 1 ? 0 : $opponentReward->quantity;
                            $rewardList[] = ["type" => "gems", "quantity" => $opponentReward->quantity];
                        } elseif ($opponentReward->item && $opponentReward->quantity) { // If mode is "item" and item is selected
                            // Skip if no storage
                            if ($userStorage < 1) {
                                $rewardList[] = ["type" => "item", "quantity" => $opponentReward->quantity, "item" => $opponentReward->item->toArray(), "storageFull" => true];
                            } else {
                                $quantity = $opponentReward->quantity;
                                if ($quantity > $userStorage) {
                                    $quantity = $userStorage;
                                }
                                $rewardList[] = ["type" => "item", "quantity" => $quantity, "item" => $opponentReward->item->toArray()];
                                if ($quantity) {
                                    $userStorage -= $quantity;
                                    for ($x = 1; $x <= $quantity; $x++) {
                                        $userItem = new UserItem;
                                        $userItem->user_id = $user->id;
                                        $userItem->item_id = $opponentReward->item_id;
                                        $userItem->location = "storage";
                                        $userItem->save();
                                    }
                                }
                            }
                        }
                    }
                }
                if ($rewardCoins > 0 || $rewardGems > 0) {
                    $user->coins += $rewardCoins;
                    $user->gems += $rewardGems;
                    $user->save();
                }
                // TODO: Resume, sum up statistics (hit, block, etc.)
            }
        }

        $fightResume = [
            "xp" => $userXp,
            "coins" => $rewardCoins,
            "gems" => $rewardGems,
            "rewards" => $rewardList
        ];

        Cache::put('fight_resume_' . $user->id, $fightResume);
        Cache::put('fight_archive_' . $user->id, $fight->id);
        if ($fight->is_pvp) {
            $fightResume['xp'] = $opponentXp;
            Cache::put('fight_resume_' . $opponent->id, $fightResume);
            Cache::put('fight_archive_' . $opponent->id, $fight->id);
        }


        return Redirect::to('/fight');
    }

    public function onLeave()
    {
        $user = Auth::getUser();
        if (!$user) {
            return false; // TODO: redirect
        }

        $fight = Fight::find($user->fight_id);
        if (!$fight) {
            return Redirect::to('/arena');
        }

        $user->lose += 1;
        // HP
        if ($user->hp < $user->hp_max) {
            $user->hp = $user->hp < 0 ? 0 : $user->hp;
            $hpRatio = config('re.hp_rate');
            $tsDiff = round(($user->hp_max - $user->hp) * $hpRatio);
            $user->hp_ts = time() + $tsDiff;
        }
        // AP
        if ($user->ap < $user->ap_max) {
            $user->ap = $user->ap < 0 ? 0 : $user->ap;
            $apRatio = config('re.ap_rate');
            $tsDiff = round(($user->ap_max - $user->ap) * $apRatio);
            $user->ap_ts = time() + $tsDiff;
        }
        $user->fight_id = null;
        $user->save();
        Cache::forget('round_data_' . $user->id);
        Cache::forget('fight_archive_' . $user->id);

        if ($fight->is_pvp) {
            $opponent = $fight->user_id != $user->id ? $fight->user : $fight->user2;
            $opponent->win += 1;
            // HP
            if ($opponent->hp < $opponent->hp_max) {
                $opponent->hp = $opponent->hp < 0 ? 0 : $opponent->hp;
                $hpRatio = config('re.hp_rate');
                $tsDiff = round(($opponent->hp_max - $opponent->hp) * $hpRatio);
                $opponent->hp_ts = time() + $tsDiff;
            }
            // AP
            if ($opponent->ap < $opponent->ap_max) {
                $opponent->ap = $opponent->ap < 0 ? 0 : $opponent->ap;
                $apRatio = config('re.ap_rate');
                $tsDiff = round(($opponent->ap_max - $opponent->ap) * $apRatio);
                $opponent->ap_ts = time() + $tsDiff;
            }
            // TODO: XP + Fight Resume
            $xpWinRatio = config("re.xp_win_ratio");
            $userHpDamanged = $user->hp_max - $user->hp;
            $opponentXp = round($userHpDamanged * $xpWinRatio);
            $opponent->xp += $opponentXp;

            $opponent->fight_id = null;
            $opponent->save();

            $fightResume = [
                "xp" => $opponentXp,
                "coins" => 0,
                "gems" => 0,
                "rewards" => []
            ];

            Cache::put('fight_resume_' . $opponent->id, $fightResume);
            Cache::put('fight_archive_' . $opponent->id, $fight->id);
        }

        // TODO: Set Winner
        $fight->winner = $fight->user_id == $user->id ? "opponent" : "user";
        $fight->archive(); // TODO: Archive Fight
        Flash::warning('You left the fight!');

        return Redirect::to('/arena');
    }
}