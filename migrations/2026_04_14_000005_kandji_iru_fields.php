<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Kandji v3.0 — Add fields exposed by the Iru-rebranded agent.
 *
 * New local preference keys available from agent v5.x+:
 *   Company     — the organisation name configured in the MDM tenant
 *   LastReport  — human-readable timestamp of the last agent report
 *   LastStatus  — current device compliance status (e.g. "Pass", "Alert")
 */
class KandjiIruFields extends Migration
{
    private $tableName = 'kandji';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->string('company')->nullable();
            $table->string('last_report')->nullable();
            $table->string('last_status')->nullable();

            $table->index('company');
            $table->index('last_status');
        });
    }

    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('last_report');
            $table->dropColumn('last_status');
        });
    }
}
