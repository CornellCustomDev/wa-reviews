#!/bin/bash

echo ".env"
export $(grep '^DB_' .env | xargs)
NAME_OF_FILE=$1
echo "Do you want to update $DB_DATABASE db with $NAME_OF_FILE? y/n"
read CONTINUE
if [[ $CONTINUE == "y" ]]
then
  echo "Here we go..."
  zcat $NAME_OF_FILE | mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD --force $DB_DATABASE
  echo "Import complete."
else
  echo "Cancelled db import."
fi
