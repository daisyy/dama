#!/bin/sh

# composer
curl -s https://getcomposer.org/installer | php
php composer.phar install

# docker
docker run -t -i -d --name dama-php -v /www/dama:/usr/local/nginx/html/dama:ro -e LISTEN=0.0.0.0:9000 andrei821/docker-php5-fpm

