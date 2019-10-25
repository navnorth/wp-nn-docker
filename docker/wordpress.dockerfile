# change the below tag to whatever (reasonable) combination of WP and PHP version you need
FROM wordpress:4.9.3-php7.2
RUN apt-get update
RUN apt-get install -y nano vim mysql-client
COPY --chown=www-data:www-data  wp-content /var/www/html
