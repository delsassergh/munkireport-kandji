#!/usr/local/munkireport/munkireport-python3

# Kandji module for MunkiReport — v3.0
# Supports both the legacy Kandji agent (/usr/local/bin/kandji) and the
# rebranded Iru agent (/usr/local/bin/iru). The preference domain remains
# io.kandji.Kandji in both cases.

import subprocess
import os
import sys
import plistlib

sys.path.insert(0, '/usr/local/munki')
sys.path.insert(0, '/usr/local/munkireport')

from Foundation import CFPreferencesCopyAppValue

# Agent binary locations — Iru (rebranded) takes precedence over legacy Kandji
AGENT_BINARIES = [
    '/usr/local/bin/iru',
    '/usr/local/bin/kandji',
]

# Preference domain — unchanged by the Iru rebrand
PREF_DOMAIN = 'io.kandji.Kandji'


def find_agent_binary():
    """Return the path of the first found agent binary, or None if not installed."""
    for path in AGENT_BINARIES:
        if os.path.isfile(path):
            return path
    return None


def get_local_kandji_prefs():
    result = dict()

    # Core fields — present in all agent versions
    result['kandji_agent_version'] = CFPreferencesCopyAppValue('AgentVersion', PREF_DOMAIN)
    result['blueprint_name'] = CFPreferencesCopyAppValue('Blueprint', PREF_DOMAIN)

    # device_id — extracted from the ComputerURL path component
    computer_url = CFPreferencesCopyAppValue('ComputerURL', PREF_DOMAIN)
    if computer_url is not None:
        result['device_id'] = computer_url.split('/')[-1]
    else:
        result['device_id'] = None

    # v3.0 — new fields exposed by Iru agent (also present on updated Kandji builds)
    result['company'] = CFPreferencesCopyAppValue('Company', PREF_DOMAIN)
    result['last_report'] = CFPreferencesCopyAppValue('LastReport', PREF_DOMAIN)
    result['last_status'] = CFPreferencesCopyAppValue('LastStatus', PREF_DOMAIN)

    return result


def get_users_info():
    """Return all local users as a list of dicts via dscl plist output."""
    cmd = ['/usr/bin/dscl', '-plist', '.', '-readall', '/Users']
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE,
                            stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()
    try:
        return plistlib.loads(output)
    except Exception:
        return {}


def get_passport_info():
    """Return a comma-separated string of shortname: linked_account pairs for
    any local user that has a Kandji/Iru Passport linked account."""
    out = []
    all_users = get_users_info()
    for user in all_users:
        if 'dsAttrTypeNative:io.kandji.KandjiLogin.LinkedAccount' in list(user.keys()):
            kandji_linked_account = user['dsAttrTypeNative:io.kandji.KandjiLogin.LinkedAccount'][0]
            user_shortname = user['dsAttrTypeStandard:RecordName'][0]
            out.append('%s: %s' % (user_shortname, kandji_linked_account))
    if len(out) > 0:
        return ', '.join(out)
    return []


def main():
    """Main"""

    # Require either the Iru or legacy Kandji agent binary to be present
    agent_binary = find_agent_binary()
    if agent_binary is None:
        print("ERROR: Neither the Iru nor Kandji agent binary was found")
        exit(0)

    # Get local preference results
    result = get_local_kandji_prefs()

    # Passport data
    passport_users = get_passport_info()
    if len(passport_users) > 0:
        result['passport_enabled'] = "True"
        result['passport_users'] = passport_users

    # Write results to cache plist
    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    output_plist = os.path.join(cachedir, 'kandji.plist')
    with open(output_plist, 'wb') as fp:
        plistlib.dump(result, fp, fmt=plistlib.FMT_XML)


if __name__ == "__main__":
    main()
