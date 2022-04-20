#!/usr/bin/env bash

# Target .env file location: 
DOTENV_FILE="/var/app/current/.env"

# Extract the Elastic Beanstalk Environment Configuration Data
ENVIRONMENT_JSON=`/opt/elasticbeanstalk/bin/get-config environment`

# Clear out any existing .env file data
cat /dev/null > $DOTENV_FILE

# Convert the ENVIRONMENT_JSON to .env file format
while read -rd $'' line
do
    echo "$line" >> $DOTENV_FILE
done < <(jq -r <<< $ENVIRONMENT_JSON \
         'to_entries|map("\(.key)=\"\(.value)\"\u0000")[]')

FILE_STORAGE_PATH=$(/opt/elasticbeanstalk/bin/get-config environment | jq -r '.FILE_STORAGE_PATH')
mkdir -p $FILE_STORAGE_PATH/public/modules
ln -sf $FILE_STORAGE_PATH/public /var/app/current/public/storage