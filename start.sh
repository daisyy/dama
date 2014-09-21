#!/bin/sh
docker run -it --rm --name dama-apache-php-app -v "$(pwd)/public":/var/www/html php:5.6-apache
