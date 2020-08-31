# change the below tag to whatever (reasonable) combination of WP and PHP version you need
FROM wordpress:5.5-php7.2-apache
RUN apt-get update
RUN apt-get install -y nano vim default-mysql-client
# we have our own copy, the WP setup will overwrite it if it isn't removed from the src
RUN rm -rf /usr/src/wordpress/wp-content
COPY --chown=www-data:www-data config/.htaccess /var/www/html
COPY --chown=www-data:www-data config/wp-config.php.default /var/www/html/wp-config.php
