#!/bin/sh
docker run -it --rm --name dama-apache-php-app -p 8001:80 -v "$(pwd)":/var/www/html php:5.6-apache sh install.sh
