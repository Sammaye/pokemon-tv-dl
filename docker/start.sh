#!/bin/bash

ROOT='/var/www/html'

cd $ROOT

if [ -f "${ROOT}/composer.json" ]; then
    composer install
fi

if [ -f "${ROOT}/package.json" ]; then
    npm install
fi
