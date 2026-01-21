<?php
// database/migrations/xxxx_create_tours_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('destination_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->integer('duration_days');
            $table->decimal('price_per_person', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('max_group_size')->default(20);
            $table->boolean('is_featured')->default(false);
            $table->integer('available_slots')->default(20);
            $table->string('status')->default('published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};