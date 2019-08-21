FROM ubuntu:18.04
MAINTAINER Vaclav Kalas <vaclav@pixman.cz>

ARG CACHEBUST=15

ARG DEBIAN_FRONTEND=noninteractive


RUN apt-get  update
RUN apt-get  upgrade -y
RUN apt-get install -y software-properties-common

# pro spravne nastaveni casove zony
RUN apt-get install tzdata -y
ENV DATE_TIMEZONE "Europe/Prague"
ENV TIME_ZONE "Europe/Prague"
ENV TZ "Europe/Prague"

ADD debconf.selections /tmp/debconf.selections
RUN debconf-set-selections /tmp/debconf.selections

RUN add-apt-repository ppa:ondrej/php

# Install selected extensions and other stuff
RUN apt-get  -o Acquire::Check-Valid-Until=false update \
    && apt-get -y --no-install-recommends install git curl unzip php7.0-mbstring php7.0-mysql php-redis php7.0-bz2 php7.0-gd php-imagick php7.0-imap php7.0-soap php7.0-tidy php7.0-xmlrpc php7.0-xsl \
        php7.0-xdebug \
    	php7.0-cgi \
    	php7.0-cli \
    	php7.0-common \
    	php7.0-curl \
    	php7.0-dev \
    	php7.0-enchant \
    	php7.0-fpm \
    	php7.0-gmp \
    	php7.0-imap \
    	php7.0-interbase \
    	php7.0-intl \
    	php7.0-json \
    	php7.0-ldap \
    	php7.0-opcache \
    	php7.0-phpdbg \
    	php7.0-pspell \
    	php7.0-readline \
    	php7.0-recode \
    	php7.0-sqlite3 \
    	php7.0-zip \
    	php7.0-sybase

RUN apt-get install apache2 libapache2-mod-php7.0 -y
RUN apt-get install mariadb-common mariadb-client -y
RUN apt-get install nano curl -y
RUN apt-get install openssl -y
RUN apt-get install imagemagick -y
RUN apt-get install -y sendmail
RUN apt-get install -y php7.0-redis
RUN apt-get install -y redis
RUN apt-get install -y cron
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash -
RUN apt-get install -y nodejs
RUN apt-get install composer tree -y
RUN npm install -g sass
RUN npm cache verify
RUN npm i -g npm
RUN npm i

ADD sendmail.ini /usr/local/etc/php/conf.d/

ENV LOG_STDOUT **Boolean**
ENV LOG_STDERR **Boolean**
ENV LOG_LEVEL warn
ENV ALLOW_OVERRIDE All
ENV TERM dumb

ADD run-server.sh /tmp/run-server.sh
RUN chmod a+x /tmp/run-server.sh

RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers

RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www

#COPY composer.json /app
#COPY ../web/composer.json /var/www/html
#COPY composer.json /var/www/html/www

#RUN chmod 777 /var/www/log temp && \
#    composer install

#RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
#    && php composer-setup.php \
#    && php -r "unlink('composer-setup.php');" \
#    && mv composer.phar /usr/bin/composer

#RUN composer.phar install

#USER user

#RUN npm install
#RUN composer install --no-scripts --no-autoloader
#RUN composer create-project



#RUN php composer.phar update monolog/monolog

#RUN composer install && \
#    rm -rf composer* Dockerfile .git && \

#RUN composer install --no-plugins --no-scripts
#RUN composer diagnose

#COPY ./composer.json /tmp/package.json

#RUN dir
RUN composer diagnose



#COPY ../composer.json ./
#RUN composer install --no-scripts --no-autoloader
#RUN composer install --no-plugins --no-scripts


ADD cron/root /etc/cron.d/root
RUN chown root:root -R /etc/cron.d
RUN chmod 0644 -R /etc/cron.d
RUN touch /var/log/cron.log
RUN crontab /etc/cron.d/root

EXPOSE 80
EXPOSE 443
EXPOSE 6379

ENTRYPOINT ["/tmp/run-server.sh"]
#CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
