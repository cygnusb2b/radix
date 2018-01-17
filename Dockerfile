FROM limit0/php56:newrelic-latest

COPY conf/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY conf/php.ini /usr/local/etc/php/conf.d/zzz_php.ini

ENV APP_ENV prod
ENV SYMFONY_ENV prod

COPY app /var/www/html/app
COPY bin /var/www/html/bin
COPY src /var/www/html/src
COPY var /var/www/html/var
COPY vendor /var/www/html/vendor
COPY web /var/www/html/web

RUN chown -R www-data:www-data var && chmod -R 0755 var
