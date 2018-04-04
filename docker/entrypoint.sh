#!/bin/bash
cd /var/www/html

php bin/composer install --no-interaction
php bin/console cache:warmup
# chown -R www-data:www-data . &&
chmod -R 0755 var/cache var/logs
php bin/console assetic:watch
