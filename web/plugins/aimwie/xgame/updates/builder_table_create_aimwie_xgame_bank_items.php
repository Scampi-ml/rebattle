<?php namespace Aimwie\Xgame\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAimwieXgameBankItems extends Migration
{
    public function up()
    {
        Schema::create('aimwie_xgame_bank_items', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->integer('item_id');
            $table->string('location'); // Bank location types: safe, pledge
            $table->integer('item_value')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aimwie_xgame_bank_items');
    }
}
