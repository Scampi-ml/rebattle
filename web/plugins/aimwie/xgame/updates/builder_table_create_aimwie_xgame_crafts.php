<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameCrafts extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_crafts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('craft_key')->nullable()->index();
            $table->boolean('has_dublicate')->nullable();
            $table->integer('item_id')->nullable();
            $table->integer('items_count')->default(0);
            $table->integer('level')->default(0);
            $table->integer('coins')->default(0);
            $table->integer('gems')->default(0);
            $table->integer('success_rate')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_crafts');
    }
}
