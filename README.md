Iru module
==========

> **Note**: This module is the continuation of [jc0b/kandji](https://github.com/jc0b/kandji), which is now archived. It has been rebranded to track the Kandji → Iru agent rename. All of the original Kandji functionality is preserved; the module name and database table have been renamed from `kandji` to `iru`. Existing installs are migrated in-place — see the **Upgrading from `jc0b/kandji`** section below.

Iru (formerly Kandji) integration for MunkiReport. Based originally on [tuxudo/jamf](https://github.com/tuxudo/jamf), forked from jc0b's Kandji module.

The Iru Admin tab within the Admin dropdown menu allows an administrator to check if MunkiReport is able to access their Iru instance, as well as some details as to how it is configured. There is the option to manually pull data for all Macs within MunkiReport.

The `php-curl` module is required. On Ubuntu/Debian: `sudo apt-get install php-curl`.

## Installation

Once published to Packagist this module will be installable via composer:

```sh
composer require delsassergh/iru
```

Until then, add this repository as a VCS source in your MunkiReport `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/delsassergh/munkireport-iru" }
],
"require": {
    "delsassergh/iru": "dev-main"
}
```

…then run `composer update delsassergh/iru` from the MunkiReport root.

## Configuration

To enable the module add the following information to the `.env` file.

```sh
IRU_ENABLE="TRUE"
IRU_API_ENDPOINT="https://[domain].clients.kandji.io/"
IRU_API_KEY="some_key_here"
IRU_TENANT_ADDRESS="https://[domain].kandji.io/"
```

The Iru API key requires only one permission: GET on Device List (`/devices`).

> Note: the Iru API hostnames are still under `kandji.io` until Kandji completes the rebrand of their backend. The endpoint values above remain correct as-is.

## Upgrading from `jc0b/kandji`

If you previously had the `jc0b/kandji` module installed, this module will:

1. Detect the existing `kandji` database table and **rename it to `iru` in place**, preserving every row of historical data.
2. Rename the `kandji_agent_version` column to `iru_agent_version` on that same table.
3. Clean up legacy `kandji.py` preflight scripts and `kandji.plist` cache files via the install/uninstall scripts.

The rename is performed by migration `2026_05_18_000004_iru_rename_table.php`. The three prior `kandji_*` migration files are intentionally left with their original filenames so that MunkiReport's migrations tracking table continues to recognize them as already-applied on existing installs.

To migrate cleanly:

1. Remove the old `jc0b/kandji` `require` entry from MunkiReport's `composer.json`.
2. Add `delsassergh/iru` as described under **Installation**.
3. Run `composer update`.
4. Run MunkiReport's migrate script (e.g. `./please migrate` or `php please.php migrate` depending on your MR version).

If you need to roll back to `jc0b/kandji` for any reason, the `down()` half of the rename migration will rename `iru` back to `kandji` and restore the column name.

## Note on the agent preference domain

The Iru agent (formerly the Kandji agent) still writes its preferences to the `io.kandji.Kandji` preference domain. The preflight script `scripts/iru.py` reads from that domain accordingly. This is expected and will continue to work until Kandji changes the domain on the client side.

## Table Schema

After this module's migrations have run, the table is `iru` with the following columns:

* `id` — increments — incremental value used by MunkiReport
* `serial_number` — string — serial number of Mac
* ~~`kandji_id` — string — *deprecated*, kept nullable for back-compat~~
* `device_id` — string — Iru/Kandji ID of Mac
* `name` — string — name of Mac in Iru
* `iru_agent_version` — string — Iru agent version (renamed from `kandji_agent_version`)
* `asset_tag` — text — Iru asset tag
* `last_check_in` — bigInteger — timestamp of last check in to Iru
* `last_enrollment` — bigInteger — timestamp of last enrollment with Iru
* `first_enrollment` — bigInteger — timestamp of first enrollment with Iru
* `blueprint_id` — string — Iru blueprint ID
* `blueprint_name` — text — name of Iru blueprint
* `realname` — text — real name of Iru blueprint
* `email_address` — string — email address of Mac's assigned user in Iru
* `passport_enabled` — string — whether Passport login is enabled
* `passport_users` — string — Passport linked accounts

## Credits

Original Kandji module: [jc0b/kandji](https://github.com/jc0b/kandji).
This fork is maintained by [@delsassergh](https://github.com/delsassergh) with the consent and encouragement of jc0b — see [jc0b/kandji#10](https://github.com/jc0b/kandji/pull/10) for the conversation that led to the fork.
