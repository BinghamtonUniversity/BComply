#!/usr/bin/env bash
FILE_STORAGE_PATH=$(/opt/elasticbeanstalk/bin/get-config environment | jq -r '.FILE_STORAGE_PATH')
mkdir -p $FILE_STORAGE_PATH/public/modules
ln -sf $FILE_STORAGE_PATH/public /var/app/current/public/storage