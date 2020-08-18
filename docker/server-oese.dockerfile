# change the below tag to whatever (reasonable) combination of WP and PHP version you need
FROM wordpress:5.3.0-php7.2
RUN apt-get update
RUN apt-get install -y nano vim default-mysql-client
RUN cd /usr/local/bin ; curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar ; chmod +x wp-cli.phar ; mv wp-cli.phar wp
COPY config/server-wp-config.php.oese /var/www/html/wp-config.php
# we have our own copy, the WP setup will overwrite it if it isn't removed from the src
RUN rm -rf /usr/src/wordpress/wp-content
