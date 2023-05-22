<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameUserItems extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_user_items', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id')->nullable()->index();
            $table->integer('item_id');
            $table->string('location');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_user_items');
    }
}
