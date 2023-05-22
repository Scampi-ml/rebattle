<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameFightRounds extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_fight_rounds', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('fight_id');
            $table->integer('user_id');
            $table->integer('user2_id')->nullable();
            $table->integer('opponent_id')->nullable();
            $table->integer('round')->default(0);
            $table->smallInteger('user_attack_id')->nullable();
            $table->smallInteger('user_block_id')->nullable();
            $table->integer('user_action_id')->nullable();
            $table->smallInteger('opponent_attack_id')->nullable();
            $table->smallInteger('opponent_block_id')->nullable();
            $table->integer('opponent_action_id')->nullable();
            $table->integer('user_hp')->default(0);
            $table->integer('user_ap')->default(0);
            $table->integer('opponent_hp')->default(0);
            $table->integer('opponent_ap')->default(0);
            $table->integer('user_hit')->default(0);
            $table->integer('opponent_hit')->default(0);
            $table->boolean('user_hit_critical')->nullable();
            $table->boolean('opponent_hit_critical')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_fight_rounds');
    }
}
