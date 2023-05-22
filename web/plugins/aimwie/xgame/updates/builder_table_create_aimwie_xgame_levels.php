<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameLevels extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_levels', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->bigInteger('xp')->default(0);
            $table->integer('points')->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_levels');
    }
}
