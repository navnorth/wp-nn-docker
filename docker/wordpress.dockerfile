FROM wordpress:4.9
RUN sed -r -e 's/\r$//' /usr/src/wordpress/wp-config-sample.php \
	| awk '/^\/\*.*stop editing.*\*\/$/ { print("define( \"WP_ALLOW_MULTISITE\", true );") } { print }' > temp.php \
	&& chown --reference /usr/src/wordpress/wp-config-sample.php temp.php \
	&& mv temp.php /usr/src/wordpress/wp-config-sample.php
RUN apt-get update
RUN apt-get install -y nano vim mysql-client
