# WordPress Dev Docker

## Pre-Setup

A few steps to run before your initial setup, just to make sure we don't run into any issues:

### Step 0

Get the Docker Desktop app installed if you don't already have it

**Ubuntu:** `> sudo apt-get update && sudo apt-get install -y docker.io docker-compose`

**Mac:** https://docs.docker.com/docker-for-mac/install/

**Windows 10:** https://docs.docker.com/docker-for-windows/install/

### Step 1

If you haven't checked out the repo yet, do that first. In this example and the rest of the readme we refer to the main repo directory on your local machine as <wp-nn-docker-directory>

    host> git clone --recurse-submodules https://github.com/navnorth/wp-nn-docker.git

If you've already cloned the repo, make sure your submodules are in place or things won't work right

    host> cd <wp-nn-docker-directory>
    host> git submodule init && git submodule update

### Step 2

This Docker only runs on local port 80 due to WordPress network limitations, so first let's make sure your host machine isn't running *anything* on port 80 already

**Most Unix:** `host> netstat -tulpn | grep --color :80`

**Mac:** `host> lsof -PiTCP -sTCP:LISTEN | grep --color :80`

If that gives you any output, you probably need to stop Apache or nginx before proceeding

    host> sudo apachectl stop && sudo nginx -s stop

### Step 3

Add localhost.localdomain to /etc/hosts. If this command doesn't work, just append the line manually to your /etc/hosts file

    host> sudo sed -i -e '$a\'$'\n''127.0.0.1   localhost.localdomain oet.localhost.localdomain oese.localhost.localdomain oii.localhost.localdomain' /etc/hosts

To test whether this worked, make sure this command finds a host

    host> ping oese.localhost.localdomain


## Start the Docker

Start up the docker from your terminal

    host> cd <wp-nn-docker-directory>
    host> docker-compose up

Open your web browser:

    http://localhost.localdomain

Need to get into the docker and tweak anything?

    host> cd <wp-nn-docker-directory>
    host> docker-compose exec wordpress bash

Login to WordPress with this admin account

    username: admin
    password: th!sN0t.H@pp3ning

## Maintenance

Occasional tasks to keep things up-to-date

### Rebuild with latest data

    host> cd <wp-nn-docker-directory>
    host> docker-compose build --no-cache db
    host> docker-compose up

### Dump the db and save it as current

    host> cd <wp-nn-docker-directory>
    host> docker-compose exec wordpress bash
    docker> mysqldump -u wordpress -h db -pwordpress wordpress > wp_db_dump.`date +%Y%m%d`.sql
    docker> exit
    host> mv html/wp_db_dump.`date +%Y%m%d`.sql docker/data/
    host> cd docker/data/
    host> ln -sf wp_db_dump.`date +%Y%m%d`.sql ./current.sql



