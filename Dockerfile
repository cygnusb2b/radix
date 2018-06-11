FROM scomm/php5.6-apache

# Copy Contents into container
COPY app /var/www/html/app
COPY bin /var/www/html/bin
COPY src /var/www/html/src
COPY var /var/www/html/var
COPY vendor /var/www/html/vendor
COPY web /var/www/html/web

# Set ENV
ENV SYMFONY_ENV=prod
ENV APP_ENV=prod

# Make sure cache in clean
RUN rm -fr var/cache/*

# Build Cache
RUN php bin/console cache:warmup --env=prod --no-debug
RUN php bin/console assets:install --env=prod
RUN php bin/console assetic:dump --env=prod --no-debug

# Set permissions
RUN chown -R www-data:www-data /var && chmod -R 0755 /var
