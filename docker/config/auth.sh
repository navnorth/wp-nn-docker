#!/bin/sh
# create http auth user wordpress/guest
htpasswd -b -c /var/www/.htpasswd wordpress guest

#set htpasswd file permission to 644
chmod 644 /var/www/.htpasswd