#!/bin/bash
#
#  Lepton L2P Package Script
#
#  Will compress the specified folder into folder.l2p after hashing the files
#

if [ "$1" == "" ]; then

	echo "Use: package.sh [packagename]"
	exit

fi

cd $1
if [ -e package.db ]; then 
	rm package.db
fi
for f in `find app -iname "*"`; do
	if [ -f $f ]; then
		md5sum $f >> package.db
	fi
done

7z a -tzip ../$1.l2p *
