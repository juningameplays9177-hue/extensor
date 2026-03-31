<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expenses')) {
            return;
        }

        if (Schema::hasColumn('expenses', 'name')) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('id');
        });

        foreach (DB::table('expenses')->whereNull('name')->get() as $row) {
            $desc = (string) ($row->description ?? '');
            $name = $desc !== '' ? mb_substr($desc, 0, 255) : '—';
            DB::table('expenses')->where('id', $row->id)->update(['name' => $name]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('expenses')) {
            return;
        }

        if (! Schema::hasColumn('expenses', 'name')) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropColumn('name');
        });
    }
};
