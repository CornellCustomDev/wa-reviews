#!/bin/bash

now=$(date +"%Y-%m-%d")
backup_dir=~/
app_env=.env
export $(grep '^DB_' $app_env | xargs)
mysqldump -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE >> $backup_dir/$DB_DATABASE-$now.sql
gzip $backup_dir/$DB_DATABASE-$now.sql
