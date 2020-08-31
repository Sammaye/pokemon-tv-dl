#!/bin/bash

# Start the Php process
php-fpm$PHP_VERSION -D
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start php-fpm: $status"
  exit $status
fi

# Start cron
service cron start

while sleep 60; do
  ps aux |grep php-fpm |grep -q -v grep
  PROCESS_1_STATUS=$?
  # If the greps above find anything, they exit with 0 status
  # If they are not both 0, then something is wrong
  if [ $PROCESS_1_STATUS -ne 0 ]; then
    echo "Php process has already exited."
    exit 1
  fi
done
