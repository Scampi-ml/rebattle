<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameItems extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_items', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->string('img_url')->nullable();
            $table->integer('price_coins')->default(0);
            $table->integer('price_gems')->default(0);
            $table->string('type_id')->nullable();
            $table->integer('level')->default(0);
            $table->integer('hp')->nullable();
            $table->integer('ap')->nullable();
            $table->integer('power')->nullable();
            $table->integer('defense')->nullable();
            $table->integer('critical')->nullable();
            $table->integer('storage_max')->nullable();
            $table->text('actions_cache')->nullable();
            $table->bigInteger('in_game')->default(0);
            $table->bigInteger('in_market')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_items');
    }
}
