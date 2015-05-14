#!/bin/bash
# Jacob Alexander 2015
# Updates all git repos
# Starts with KiiConf then calls update_controller.bash
#

git pull --rebase
./update_controller.bash

