#!/bin/bash
# Jacob Alexander 2015

CODE_PATH="controller"
URL="https://github.com/kiibohd/controller.git"
BRANCH="master"

# Check if controller code already exists
if [ ! -d "${CODE_PATH}" ]; then
	git clone "${URL}"
	#mkdir -p "${CODE_PATH}"/build
	#chmod -R a+wr "${CODE_PATH}"/build
else
	cd "${CODE_PATH}"
	git pull --rebase origin ${BRANCH}
fi

