# change the below tag to whatever (reasonable) combination of WP and PHP version you need
FROM wordpress:5.7-php7.4
RUN apt-get update
RUN apt-get install -y nano vim default-mysql-client

# we have our own copy, the WP setup will overwrite it if it isn't removed from the src
RUN rm -rf /usr/src/wordpress/wp-content
COPY --chown=www-data:www-data config/.htaccess /var/www/html/.htaccess
COPY --chown=www-data:www-data config/wp-config.php.oer /var/www/html/wp-config.php

# copy the shell script that creates auth user
COPY config/auth.sh /tmp
RUN chmod +x /tmp/auth.sh
RUN sh /tmp/auth.sh