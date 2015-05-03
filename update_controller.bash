#!/bin/bash
# Jacob Alexander 2015

CODE_PATH="controller"
KLL_PATH="kll"
URL="https://github.com/kiibohd/controller.git"
KLL_URL="https://github.com/kiibohd/kll.git"
BRANCH="master"

# Check if controller code already exists
if [ ! -d "${CODE_PATH}" ]; then
	git clone "${URL}"
	cd ${CODE_PATH}
	git clone "${KLL_URL}"
else
	cd "${CODE_PATH}"
	git pull --rebase origin ${BRANCH}
	cd "${KLL_PATH}"
	git pull --rebase origin ${BRANCH}
fi

