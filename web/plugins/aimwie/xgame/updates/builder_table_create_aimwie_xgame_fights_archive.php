<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameFightsArchive extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_fights_archive', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('original_id')->index();
            
            $table->boolean('is_pvp')->nullable();
            $table->integer('user_id');
            $table->integer('user2_id')->nullable();
            $table->integer('opponent_id')->nullable();
            
            $table->integer('user_attack_id')->nullable();
            $table->integer('user_block_id')->nullable();
            $table->integer('user_action_id')->nullable();

            $table->integer('user2_attack_id')->nullable();
            $table->integer('user2_block_id')->nullable();
            $table->integer('user2_action_id')->nullable();

            $table->integer('opponent_hp');
            $table->integer('opponent_ap');

            $table->integer('round')->nullable();

            $table->string('winner')->nullable(); // user, user2, opponent, standoff

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_fights_archive');
    }
}
