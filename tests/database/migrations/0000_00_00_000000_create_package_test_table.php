<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zacs_favorite_books', function (Blueprint $table) {
            // Id
            $table->bigIncrements('id');

            // Content
            $table->text('title');
            $table->jsonb('authors');
            $table->jsonb('notes')->nullable();

            // Meta
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zacs_favorite_books');
    }
}
