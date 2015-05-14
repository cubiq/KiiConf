#!/bin/bash
# Jacob Alexander 2015
# Build error finder

for dir in $(find tmp -mindepth 1 -maxdepth 1 -type d); do
	cd $dir

	# Attempt to build
	echo "Building - $dir"
	make -j

	# Make sure any new files are still owned by the www-data user
	chown -R www-data *

	# If failed, stop
	if [ $? -ne 0 ]; then
		exit 1
	fi

	cd -
done

echo "SUCCESS!"

