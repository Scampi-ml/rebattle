<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddUserFields extends Migration
{
    public $cols = [
        'stripe_id',
        'player_name',
        'avatar_id',
        'img_url',
        'coins',
        'gems',
        'level',
        'add_points',
        'xp',
        'xp_max',
        'hp',
        'hp_max',
        'hp_ts',
        'ap',
        'ap_max',
        'ap_ts',
        'power',
        'defense',
        'critical',
        'storage',
        'storage_max',
        'fight_id',
        'is_pvp',
        'win',
        'lose',
        'standoff',
        'items_cache',
        'actions_cache',
    ];
    public function up()
    {
        if (Schema::hasColumns('users', $this->cols)) { return; }

        Schema::table('users', function($table)
        {
            $table->string('stripe_id')->nullable();
            $table->string('player_name')->nullable();
            $table->integer('avatar_id')->nullable();
            $table->string('img_url')->nullable();
            $table->bigInteger('coins')->default(0);
            $table->bigInteger('gems')->default(0);
            $table->integer('level')->default(0);
            $table->integer('add_points')->default(0);
            $table->bigInteger('xp')->default(0); // Experience points
            $table->bigInteger('xp_max')->default(0); // Experience points for next level

            $table->integer('hp')->default(0);
            $table->integer('hp_max')->default(0);

            $table->integer('hp_ts')->nullable(); // HP renew timestamp (sec)

            $table->integer('ap')->default(0);
            $table->integer('ap_max')->default(0);
            $table->integer('ap_ts')->nullable(); // AP renew timestamp (sec)

            $table->integer('power')->default(0); // Hit power
            $table->integer('defense')->default(0); // Defense
            $table->integer('critical')->default(0); // Crtical hit (procent to get x2, x3, hit)
            $table->integer('storage')->default(0); // Storage now
            $table->integer('storage_max')->default(0); // Storage max

            $table->integer('fight_id')->nullable();
            $table->boolean('is_pvp')->nullable(); // PVP Enabled mode

            $table->integer('win')->default(0);
            $table->integer('lose')->default(0);
            $table->integer('standoff')->default(0);

            $table->text('items_cache')->nullable();
            $table->text('actions_cache')->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumns('users', $this->cols)) { return; }
            Schema::table('users', function ($table) {
                $table->dropColumn($this->cols);
            });
        }
    }
}