#!/bin/sh

# composer
curl -s https://getcomposer.org/installer | php
php composer.phar install

# docker
docker run -t -i -d --name dama-php -v /www/dama/php.ini:/etc/php5/fpm/php.ini -v /www/dama:/usr/local/nginx/html/dama:ro daisy-php
