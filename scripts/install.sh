#!/bin/bash

# Iru controller
CTL="${BASEURL}index.php?/module/iru/"

# Get the scripts in the proper directories
"${CURL[@]}" "${CTL}get_script/iru.py" -o "${MUNKIPATH}preflight.d/iru.py"

# Check exit status of curl
if [ $? = 0 ]; then
	# Make executable
	chmod a+x "${MUNKIPATH}preflight.d/iru.py"

	# Set preference to include this file in the preflight check
	setreportpref "iru" "${CACHEPATH}iru.plist"

else
	echo "Failed to download all required components!"
	rm -f "${MUNKIPATH}preflight.d/iru.py"

	# Signal that we had an error
	ERR=1
fi

# Delete legacy Kandji module artifacts left over from the jc0b/kandji module
rm -f "${CACHEPATH}kandji.txt"
rm -f "${CACHEPATH}kandji.plist"
rm -f "${MUNKIPATH}preflight.d/kandji"
rm -f "${MUNKIPATH}preflight.d/kandji.py"
rm -f "${MUNKIPATH}scripts/kandji"
rm -f "${MUNKIPATH}postflight.d/kandji"
