# WordPress Dev Docker

## Pre-Setup

A few steps to run before your initial setup, just to make sure we don't run into any issues:

### Step 0

###### step 0.1 - Get the Docker Desktop app installed if you don't already have it

	**Ubuntu:** `> sudo apt-get update && sudo apt-get install -y docker.io docker-compose`

	**Mac:** https://docs.docker.com/docker-for-mac/install/

	**Windows 10:** https://docs.docker.com/docker-for-windows/install/

###### step 0.2 - If using Windows: Install GIT for windows (https://gitforwindows.org/)

	**You can choose to use GIT BASH in running command lines.

        **or if you prefer to do GIT and BASH in Windows command prompt too,
          make sure the following paths are set in your windows environment - system variables

	      C:\Program Files\Git\cmd
          C:\Program Files\Git\usr\bin

          Just change the path above if you installed GIT in a different location.

### Step 1

If you haven't checked out the repo yet, do that first. In this example and the rest of the readme we refer to the main repo directory on your local machine as <wp-nn-docker-directory>

    host> bash init.sh

### Step 2

This Docker only runs on local port 80 due to WordPress network limitations, so first let's make sure your host machine isn't running *anything* on port 80 already

**Most Unix:** `host> netstat -tulpn | grep --color :80`

**Mac:** `host> lsof -PiTCP -sTCP:LISTEN | grep --color :80`

If that gives you any output, you probably need to stop Apache or nginx before proceeding

    host> sudo apachectl stop && sudo nginx -s stop

If what you have running on port 80 is another docker, find the Container ID and stop the Docker already running on port 80:

    host> docker ps -a | grep ":80->80"
    host> docker stop <id>

### Step 3

Add localhost.localdomain to /etc/hosts. If this command doesn't work, just append the line manually to your /etc/hosts file

    host> sudo sed -i -e '$a\'$'\n''127.0.0.1   localhost.localdomain oer.localhost.localdomain k12.localhost.localdomain' /etc/hosts

To test whether this worked, make sure this command finds a host

    host> ping oer.localhost.localdomain

## Configure docker-compose.yml

- Configure docker-compose.yml for as many development sites as necessary.
--Be sure to set the **VIRTUAL_HOST** and **WORDPRESS_*** environment variables as appropriate.
---VIRTUAL_HOST controls what URL(s) the container will respond to, and must match what you defined in your hosts file.
---WORDPRESS_DB_NAME is used to name the backing DB and must be unique for each site.
- If you have MySQL data to import, place the .sql file in the **data** directory and it will be read on container creation.
-- When using mysqldump to create the above .sql file, use the **--databases** option to ensure that the CREATE DATABASE command is included in the dump.  The database name must match the appropriate environment variable in docker-compose.

## Start the Docker

Start up the docker from your terminal

    host> cd <wp-nn-docker-directory>
    host> docker-compose up

If you get this error: 'Bind for 0.0.0.0:80 failed: port is already allocated', refer back to *Step 2* for stopping other dockers.

Wait a couple minutes for all initialization to complete.  Open your web browser to one of the URLs you defined.  You'll be presented with the standard Wordpress setup.

If no errors, open your web browser:

    http://oer.localhost.localdomain
    http://k12.localhost.localdomain

Login to WordPress with this admin account

**username:** `admin`
**password:** `th!sN0t.H@pp3ning`

Need to get into the docker and tweak anything?

    host> cd <wp-nn-docker-directory>
    host> docker-compose exec wordpress bash

### WP Solr Pro Plugin

If you need to use WP Solr, you can get the pro plugin from here, assuming you have access rights:

**v20.9:** https://drive.google.com/open?id=1Ly8we78Q_Ff-8FFPRlqxkzLoVNwB7wtK
**v21.1:** https://drive.google.com/open?id=1LqcSII8ftcfuVxEBJISNROibNeLOwm-E


## Maintenance

Occasional tasks to keep things up-to-date

### Refresh to the latest data

    host> cd <wp-nn-docker-directory>
    host> docker-compose build --no-cache db
    host> docker-compose up --detach
    host> docker-compose exec db bash
    host> mysql -u root -h db -psomewordpress wpoer < /docker-entrypoint-initdb.d/wpoer.sql

### Dump the db and save it as current

    host> cd <wp-nn-docker-directory>
    host> docker-compose exec db /usr/bin/mysqldump -u root --password=somewordpress wpoer > wp_db_dump.sql
    host> mv wp_db_dump.sql docker/data/
    host> cd docker/data/
    host> ln -sf wp_db_dump.sql ./wpoer.sql

