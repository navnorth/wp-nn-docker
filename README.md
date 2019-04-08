# WordPress Dev Docker

## Pre-Setup

A few steps to run before your initial setup, just to make sure we don't run into any issues:

1) This only runs on local port 80 due to WordPress network limitations, so first let's make sure your host machine isn't running anything on port 80 already

Most Unix:
    netstat -tulpn | grep --color :80

Mac:
    lsof -PiTCP -sTCP:LISTEN | grep --color :80


2) Add localhost.localdomain to /etc/hosts

    sudo sed -i -e '$a\'$'\n''127.0.0.1   localhost.localdomain oet.localhost.localdomain oese.localhost.localdomain oii.localhost.localdomain' /etc/hosts

if network site names are known, also add site1.localhost.localdomain etc.

## Start

Start up the docker from your terminal

    host> cd <wp-nn-docker-directory>
    host> docker-compose up

Open your web browser:

    http://oii.localhost.localdomain
    http://oet.localhost.localdomain
    http://oese.localhost.localdomain

Need to get into the docker and tweak anything?

    host> cd <wp-nn-docker-directory>
    host> docker-compose exec wordpress bash

Login to WordPress with this admin account

    username: admin
    password: th!sN0t.H@pp3ning


