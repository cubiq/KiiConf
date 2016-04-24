#!/bin/bash
# Jacob Alexander 2015-2016
# Updates all git repos
# Starts with KiiConf then calls update_controller.bash
#

cd $(dirname $(readlink -f $0))/..

git pull --rebase
tools/update_controller.bash

