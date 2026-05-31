<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }

            if (!Schema::hasColumn('notifications', 'event_id')) {
                $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('notifications', 'type')) {
                $table->string('type');
            }

            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title');
            }

            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message');
            }

            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
