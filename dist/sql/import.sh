#!/bin/bash

if [ "$2" == "" ]; then
	echo "Use: `basename $0` user db"
	exit 1
fi

echo "" > .sqltmp
for S in *.sql; do
	# echo -n "$S: "
	cat $S >> .sqltmp
	# if [ "$?" == "0" ]; then echo "Ok"; fi
done

mysql -u $1 -p $2 < .sqltmp

rm .sqltmp
