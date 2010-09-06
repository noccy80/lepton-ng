#!/bin/bash
cd $1
test -e package.db && rm package.db
echo "Finding in $1"
for FILE in `find app -iname "*" | grep -v "\.svn"`; do
	if [ -f ${FILE} ]; then 
		echo "Hashing ${FILE}"
		md5sum ${FILE} >> package.db
	fi
done
7z a -tzip ../$1.l2p * > /dev/null
