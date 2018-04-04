FROM limit0/php56:imagick-latest

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zzz_php.ini

# Set ENV
ENV SYMFONY_ENV=dev
ENV APP_ENV=dev

ADD docker/entrypoint.sh /app/entrypoint.sh

RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/sbin/apache2
USER www-data
