<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\{Meeting,User};

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meeting_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Meeting::class);
            $table->foreignIdFor(User::class);
            $table->string('user_type',10);
            $table->time('attended_from')->nullable();
            $table->time('attended_to')->nullable();
            $table->string('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_lines');
    }
};
