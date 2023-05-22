<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameAvatars extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_avatars', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title')->nullable();
            $table->integer('level')->default(0);
            $table->integer('coins')->default(0);
            $table->integer('gems')->default(0);
            $table->string('img_url')->nullable();
            $table->boolean('is_public')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_avatars');
    }
}
