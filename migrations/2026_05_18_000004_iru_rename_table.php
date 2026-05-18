<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Rename the legacy `kandji` table to `iru` and the
 * `kandji_agent_version` column to `iru_agent_version`.
 *
 * Why this migration exists
 * -------------------------
 * The previous module published as `jc0b/kandji` created and populated
 * a database table named `kandji`. This module is the continuation of
 * that work, rebranded to `iru` (Kandji's Iru agent rebrand).
 *
 * If we simply shipped new `iru_*` migrations that created a fresh
 * `iru` table, every existing customer upgrading from `jc0b/kandji`
 * would end up with an empty `iru` table alongside their populated
 * `kandji` table — losing all of their historical Iru/Kandji data.
 *
 * Instead, this migration renames the existing table (and the
 * `kandji_agent_version` column) in place, so upgrades preserve data.
 *
 * The three earlier migration files in this module
 * (2022_08_26_..._kandji_initial, 2023_02_07_..._kandji_passport,
 *  2025_05_19_..._kandji_device_id) are intentionally left with their
 * original filenames and class names so MunkiReport's migrations
 * tracking table recognizes them as already-run on existing installs.
 *
 * Reference pattern:
 * https://github.com/munkireport/users/blob/master/migrations/2020_06_27_111904_users_rename_table.php
 */
class IruRenameTable extends Migration
{
    private $oldTableName = 'kandji';
    private $newTableName = 'iru';

    public function up()
    {
        $capsule = new Capsule();
        $schema = $capsule::schema();

        // 1. Rename the table itself.
        //
        //    - On upgrades from jc0b/kandji: the `kandji` table exists
        //      and the `iru` table does not — rename it.
        //    - On a fresh install: migrations 0001-0003 just created
        //      the `kandji` table — rename it on the way through.
        //    - On a re-run (defensive): if `iru` already exists, skip.
        if ($schema->hasTable($this->newTableName)) {
            // Already renamed (idempotent guard). Nothing to do.
        } elseif ($schema->hasTable($this->oldTableName)) {
            $schema->rename($this->oldTableName, $this->newTableName);
        }

        // 2. Rename the `kandji_agent_version` column to `iru_agent_version`.
        //    Guarded so it's safe to re-run.
        if ($schema->hasTable($this->newTableName)
            && $schema->hasColumn($this->newTableName, 'kandji_agent_version')
            && ! $schema->hasColumn($this->newTableName, 'iru_agent_version')) {

            $schema->table($this->newTableName, function (Blueprint $table) {
                $table->renameColumn('kandji_agent_version', 'iru_agent_version');
            });
        }
    }

    public function down()
    {
        $capsule = new Capsule();
        $schema = $capsule::schema();

        // Reverse the column rename first, then the table rename.
        if ($schema->hasTable($this->newTableName)
            && $schema->hasColumn($this->newTableName, 'iru_agent_version')
            && ! $schema->hasColumn($this->newTableName, 'kandji_agent_version')) {

            $schema->table($this->newTableName, function (Blueprint $table) {
                $table->renameColumn('iru_agent_version', 'kandji_agent_version');
            });
        }

        if ($schema->hasTable($this->newTableName) && ! $schema->hasTable($this->oldTableName)) {
            $schema->rename($this->newTableName, $this->oldTableName);
        }
    }
}
