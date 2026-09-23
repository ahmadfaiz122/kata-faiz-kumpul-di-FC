<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->date('issued_date')->nullable()->after('tanggal_terbit');
            $table->date('expiry_date')->nullable()->after('kadaluwarsa');
        });

        foreach (DB::table('achievements')->select('id', 'tanggal_terbit', 'kadaluwarsa')->get() as $achievement) {
            DB::table('achievements')->where('id', $achievement->id)->update([
                'issued_date' => $this->legacyDate($achievement->tanggal_terbit),
                'expiry_date' => $this->legacyDate($achievement->kadaluwarsa),
            ]);
        }

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn(['tanggal_terbit', 'kadaluwarsa']);
            $table->renameColumn('issued_date', 'tanggal_terbit');
            $table->renameColumn('expiry_date', 'kadaluwarsa');
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->integer('issued_legacy')->nullable()->after('tanggal_terbit');
            $table->integer('expiry_legacy')->nullable()->after('kadaluwarsa');
        });

        foreach (DB::table('achievements')->select('id', 'tanggal_terbit', 'kadaluwarsa')->get() as $achievement) {
            DB::table('achievements')->where('id', $achievement->id)->update([
                'issued_legacy' => $this->legacyNumber($achievement->tanggal_terbit),
                'expiry_legacy' => $this->legacyNumber($achievement->kadaluwarsa),
            ]);
        }

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn(['tanggal_terbit', 'kadaluwarsa']);
            $table->renameColumn('issued_legacy', 'tanggal_terbit');
            $table->renameColumn('expiry_legacy', 'kadaluwarsa');
        });
    }

    private function legacyDate(mixed $value): ?string
    {
        if (! $value) return null;
        $raw = str_pad((string) $value, 4, '0', STR_PAD_LEFT);
        $year = 2000 + (int) substr($raw, 0, 2);
        $month = max(1, min(12, (int) substr($raw, 2, 2)));
        return sprintf('%04d-%02d-01', $year, $month);
    }

    private function legacyNumber(?string $value): ?int
    {
        return $value ? (int) date('ym', strtotime($value)) : null;
    }
};