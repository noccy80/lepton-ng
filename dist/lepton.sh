#!/bin/bash

# Update these as needed
BASE_PATH="/opt/lepton-ng/"
SYS_PATH="${BASE_PATH}sys/"
APP_PATH="./app/"

# Call on the script
"${BASE_PATH}bin/lepton" "$@"
