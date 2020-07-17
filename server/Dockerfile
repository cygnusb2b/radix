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

# Set permissions
RUN chown -R www-data:www-data /var && chmod -R 0755 /var
