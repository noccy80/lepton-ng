#!/bin/bash

if [ "$3" == "" ]; then
	echo "Use: `basename $0` user pass db"
	exit 1
fi

for S in *.sql; do
	echo -n "$S: "
	mysql -u $1 -p$2 $3 < $S
	if [ "$?" == "0" ]; then echo "Ok"; fi
done
