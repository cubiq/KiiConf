#!/bin/bash
# Jacob Alexander 2015
# Updates all git repos
# Starts with KiiConf then calls update_controller.bash
#

cd $(dirname $(readlink -f $0))/..

git pull --rebase
./update_controller.bash

