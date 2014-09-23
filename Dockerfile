FROM andrei821/docker-php5-fpm
ENV LISTEN 0.0.0.0:9000
ADD . /usr/local/nginx/html/dama
ADD /var/log/app/dama /var/log/app/dama
CMD chmod -R 0777 /var/log/app/dama
