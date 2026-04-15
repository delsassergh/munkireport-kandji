Kandji module for MunkiReport — v3.0
=====================================

> **Actively maintained fork** by [@delsassergh](https://github.com/delsassergh).  
> Originally authored by [jc0b](https://github.com/jc0b/munkireport-kandji). v3.0 adds
> support for the **Iru-rebranded Kandji agent** (v5.x+) while remaining fully compatible
> with environments still running the legacy Kandji binary.
>
> **Note:** When the Iru rebrand is complete, this module will be spun off as
> **Iru Module v1.0**. Until then, the Kandji module name and database table are preserved
> for backward compatibility.

---

Kandji integration for MunkiReport. Based heavily on [tuxudo/jamf](https://github.com/tuxudo/jamf).

The Kandji Admin tab within the Admin dropdown menu allows an administrator to verify
that MunkiReport can reach their Kandji/Iru instance, and to manually trigger a full
data pull for all enrolled Macs.

The `php-curl` module is required. Install on Ubuntu/Debian with:

```sh
sudo apt-get install php-curl
```

## What's new in v3.0

- **Dual-binary agent detection** — the script now looks for `/usr/local/bin/iru` first
  (Iru-rebranded agent) and falls back to `/usr/local/bin/kandji` (legacy). Environments
  that have already updated to the Iru agent will no longer silently produce no data.
- **Three new data fields** pulled from local agent preferences:
  - `company` — organisation name as configured in the MDM tenant
  - `last_report` — timestamp of the most recent agent report
  - `last_status` — current device compliance status (`Pass` / `Alert`)
- **Status badge** in the client detail widget — `last_status` is now displayed as a
  colour-coded badge (green for Pass, red for Alert).
- Fixed an indentation bug in the cache-write block of `kandji.py`.

## Configuration

Add the following to your MunkiReport `.env` file:

```sh
KANDJI_ENABLE="TRUE"
KANDJI_API_ENDPOINT="https://yoursubdomain.api.kandji.io/"
KANDJI_API_KEY="your_api_key_here"
KANDJI_TENANT_ADDRESS="https://yoursubdomain.kandji.io/"
```

The API key requires only one permission: **GET** on Device List (`/devices`).

## Agent compatibility

| Agent binary | Preference domain | Supported |
|---|---|---|
| `/usr/local/bin/iru` | `io.kandji.Kandji` | ✅ v3.0+ |
| `/usr/local/bin/kandji` | `io.kandji.Kandji` | ✅ all versions |

The preference domain `io.kandji.Kandji` is unchanged by the Iru rebrand — no
configuration changes are needed on client machines.

## Database schema

| Column | Type | Source | Notes |
|---|---|---|---|
| `id` | increments | — | MunkiReport internal |
| `serial_number` | string | local | Unique |
| `device_id` | string | local | Kandji/Iru computer UUID |
| `name` | string | API | Device name in Kandji |
| `kandji_agent_version` | string | local | Agent version string |
| `asset_tag` | text | API | |
| `last_check_in` | bigInteger | API | Unix epoch |
| `last_enrollment` | bigInteger | API | Unix epoch |
| `first_enrollment` | bigInteger | API | Unix epoch |
| `blueprint_id` | string | API | |
| `blueprint_name` | text | local + API | |
| `realname` | text | API | Assigned user full name |
| `email_address` | string | API | Assigned user email |
| `passport_enabled` | string | local | "True" if Passport linked accounts detected |
| `passport_users` | string | local | Comma-separated `shortname: email` pairs |
| `company` | string | local | **v3.0** Organisation name from agent prefs |
| `last_report` | string | local | **v3.0** Last agent report timestamp |
| `last_status` | string | local | **v3.0** Device compliance status |

## Upgrading from v2.x

Run MunkiReport's standard migration step — migration `000005` will add the three
new columns to your existing `kandji` table. No manual SQL required.
