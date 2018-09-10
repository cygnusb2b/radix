FROM limit0/php56:imagick-latest
# RUN pecl install xdebug-2.5.5 && docker-php-ext-enable xdebug

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zzz_php.ini

# Set ENV
ENV SYMFONY_ENV=dev
ENV APP_ENV=dev

ADD docker/entrypoint.sh /app/entrypoint.sh

VOLUME /var/www/html
VOLUME /var/www/html/var/cache
VOLUME /var/www/html/vendor

RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/sbin/apache2
# USER www-data
