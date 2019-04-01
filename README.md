- add localhost.localdomain to /etc/hosts
- if network site names are known, also add site1.localhost.localdomain etc.
- docker-compose up
- normal Wordpress Setup; use localhost.localdomain for site URL
- Tools / Network Setup
- choose Sub-domains option
- docker-compose exec wordpress bash
- Follow instructions for wp-config.php and .htaccess modifications

* Note - only runs on local port 80 due to WordPress network limitations


