#!/bin/sh

# composer
curl -s https://getcomposer.org/installer | php
php composer.phar install

# docker
docker build -t dama-php .
docker run -t -i -d --name php dama-php

