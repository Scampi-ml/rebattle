<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameOpponentRewards extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_opponent_rewards', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('opponent_id')->index();
            $table->string('reward_type')->nullable(); // Item, Coins, Gems
            $table->integer('item_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('success_rate')->nullable();
            $table->integer('success_rate_position')->nullable()->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_opponent_rewards');
    }
}
