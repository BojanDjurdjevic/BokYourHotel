#!/bin/sh

cd /home/u544493130/domains/bookyourhotelapp.com/public_html || exit 1

/opt/alt/php83/usr/bin/php artisan queue:work \
  --queue=default \
  --tries=3 \
  --timeout=60 \
  --stop-when-empty \
  >> storage/logs/queue-cron.log 2>&1