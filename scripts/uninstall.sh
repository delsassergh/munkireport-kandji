#!/bin/bash

# Remove iru script
rm -f "${MUNKIPATH}preflight.d/iru.py"

# Remove iru.plist file
rm -f "${CACHEPATH}iru.plist"

# Remove legacy kandji-named artifacts if present (from previous jc0b/kandji install)
rm -f "${MUNKIPATH}preflight.d/kandji.py"
rm -f "${CACHEPATH}kandji.plist"
