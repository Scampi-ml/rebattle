<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameOpponents extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_opponents', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('player_name')->nullable();
            $table->integer('hp_max')->default(0);
            $table->integer('ap_max')->default(0);
            $table->integer('power')->default(0);
            $table->integer('defense')->default(0);
            $table->integer('critical')->default(0);
            $table->string('avatar_mode')->nullable();
            $table->integer('avatar_id')->nullable();
            $table->string('img_url')->nullable();
            $table->integer('level')->default(0);
            $table->integer('min_level')->default(0);
            $table->boolean('is_public')->nullable();
            $table->text('items_cache')->nullable();
            $table->text('actions_cache')->nullable();
            $table->string('reward_mode')->default(0);
            $table->integer('reward_size')->default(0);
            $table->integer('rewards_count')->default(0);
            $table->integer('rewards_sr_sum')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_opponents');
    }
}
