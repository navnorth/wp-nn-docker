# change the below tag to whatever (reasonable) combination of WP and PHP version you need
FROM wordpress:5.3.0-php7.2
RUN apt-get update
RUN apt-get install -y nano vim default-mysql-client
#RUN apt-get install -y git

# we have our own copy, the WP setup will overwrite it if it isn't removed from the src
RUN rm -rf /usr/src/wordpress/wp-content
COPY --chown=www-data:www-data config/.htaccess /var/www/html
COPY --chown=www-data:www-data config/wp-config.php.oer /var/www/html/wp-config.php

#RUN mkdir -p wp-content/plugins/wp-oer
#WORKDIR wp-content/plugins/wp-oer
#RUN git clone --branch mpa-stage https://github.com/navnorth/wp-oer.git wp-oer

#RUN mkdir -p wp-content/plugins/wp-curriculum
#WORKDIR wp-content/plugins/wp-curriculum
#RUN git clone --branch mpa-stage https://github.com/navnorth/wp-curriculum.git

#RUN mkdir -p wp-content/plugins/wp-academic-standards
#WORKDIR wp-content/plugins/wp-academic-standards
#RUN git clone https://github.com/navnorth/wp-academic-standards.git
