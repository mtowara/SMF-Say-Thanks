# based on https://github.com/vortexau/SMF-Docker
FROM php:7.2.6-apache

RUN apt-get update \
    && mkdir -p /usr/share/man/man1 \
    && mkdir -p /usr/share/man/man7 \
    && apt-get install -y sudo apt-utils libpq-dev postgresql htop nano curl wget libcurl4-openssl-dev \
    && docker-php-ext-install pgsql pdo pdo_pgsql

RUN service postgresql start; \
    sudo -u postgres psql -c 'CREATE DATABASE smfdb;' \
    && sudo -u postgres psql -c "CREATE USER smfuser WITH ENCRYPTED PASSWORD 'smfpass';" \
    && sudo -u postgres psql -c 'GRANT ALL PRIVILEGES ON DATABASE smfdb TO smfuser;'

EXPOSE 80/tcp

# Download SMF
RUN mkdir -p /var/www/html \
    && cd /var/www/html \
    && wget "https://download.simplemachines.org/index.php/smf_2-0-15_install.tar.gz" \
    && tar zxf smf_2-0-15_install.tar.gz

# Set the permissions SMF wants. They say 777 suggested!
#RUN chmod 777 /var/www/html/attachments \
#    /var/www/html/avatars \
#    /var/www/html/cache \
#    /var/www/html/Packages \
#    /var/www/html/Packages/installed.list \
#    /var/www/html/Smileys \
#    /var/www/html/Themes \
#    /var/www/html/agreement.txt \
#    /var/www/html/Settings.php \
#    /var/www/html/Settings_bak.php

# only for debugging!! choose sensible settings for production...
RUN chmod -R a+rw /var/www/html

CMD service postgresql start && /usr/sbin/apachectl -DFOREGROUND
