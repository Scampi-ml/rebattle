<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameItemActions extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_item_actions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('item_id');
            $table->integer('action_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_item_actions');
    }
}
