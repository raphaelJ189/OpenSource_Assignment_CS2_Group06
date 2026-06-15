FROM php:8.3-apache

RUN a2enmod headers
RUN docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/
COPY docker-entrypoint.sh /usr/local/bin/srms-entrypoint

RUN chmod +x /usr/local/bin/srms-entrypoint \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["srms-entrypoint"]
