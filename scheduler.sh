#!/bin/sh

cd /home/u544493130/domains/bookyourhotelapp.com/public_html || exit 1

/opt/alt/php83/usr/bin/php artisan schedule:run \
  >> storage/logs/scheduler-cron.log 2>&1