#!/usr/local/munkireport/munkireport-python3

# Iru module preflight script.
#
# Reads local agent preferences. Note: the Iru agent (formerly Kandji)
# still writes to the io.kandji.Kandji preference domain, so we read
# from that domain even though the module is named "iru".

import subprocess
import os
import sys
import plistlib

sys.path.insert(0, '/usr/local/munki')
sys.path.insert(0, '/usr/local/munkireport')

from Foundation import CFPreferencesCopyAppValue

# Iru agent still uses the io.kandji.Kandji preference domain
IRU_PREFERENCE_DOMAIN = 'io.kandji.Kandji'

def get_local_iru_prefs():
    result = dict()
    result['iru_agent_version'] = CFPreferencesCopyAppValue('AgentVersion', IRU_PREFERENCE_DOMAIN)
    result['blueprint_name'] = CFPreferencesCopyAppValue('Blueprint', IRU_PREFERENCE_DOMAIN)
    result['device_id'] = CFPreferencesCopyAppValue('ComputerURL', IRU_PREFERENCE_DOMAIN)
    if result['device_id'] is not None:
        result['device_id'] = result['device_id'].split('/')[-1]
    return result

def get_users_info():
    # Get all users info as plist
    cmd = ['/usr/bin/dscl', '-plist', '.', '-readall', '/Users']
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()

    try:
        try:
            return plistlib.readPlistFromString(output)
        except AttributeError:
            return plistlib.loads(output)
    except Exception:
        return {}

def get_passport_info():
    out = []
    all_users = get_users_info()
    for user in all_users:
        if 'dsAttrTypeNative:io.kandji.KandjiLogin.LinkedAccount' in list(user.keys()):
            iru_linked_account = user['dsAttrTypeNative:io.kandji.KandjiLogin.LinkedAccount'][0]
            user_shortname = user['dsAttrTypeStandard:RecordName'][0]
            out.append('%s: %s' % (user_shortname, iru_linked_account))
    if len(out) > 0:
        return ', '.join(out)
    return []

def main():
    """Main"""

    # Iru binary lives at /usr/local/bin/iru; /usr/local/bin/kandji is the legacy compat shim
    if not os.path.isfile('/usr/local/bin/iru') and not os.path.isfile('/usr/local/bin/kandji'):
        print("ERROR: The Iru (Kandji) agent is not installed")
        sys.exit(0)

    # Get results
    result = get_local_iru_prefs()
    passport_users = get_passport_info()
    if len(passport_users) > 0:
        result['passport_enabled'] = "True"
        result['passport_users'] = passport_users

    # Write results to cache
    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    output_plist = os.path.join(cachedir, 'iru.plist')
    try:
        plistlib.writePlist(result, output_plist)
    except Exception:
        with open(output_plist, 'wb') as fp:
            plistlib.dump(result, fp, fmt=plistlib.FMT_XML)

if __name__ == "__main__":
    main()
