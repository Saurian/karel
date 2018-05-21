FROM ubuntu:latest
MAINTAINER Jan Pagac <jan.pagac@pixman.cz>

RUN apt-get update && apt-get upgrade -y

#ADD debconf.selections /tmp/debconf.selections
#RUN debconf-set-selections /tmp/debconf.selections

RUN apt-get update && apt-get upgrade -y && apt-get install -y \
	php7.0 \
	php7.0-bz2 \
	php7.0-cgi \
	php7.0-cli \
	php7.0-common \
	php7.0-curl \
	php7.0-dev \
	php7.0-enchant \
	php7.0-fpm \
	php7.0-gd \
	php7.0-gmp \
	php7.0-imap \
	php7.0-interbase \
	php7.0-intl \
	php7.0-json \
	php7.0-ldap \
	php7.0-mcrypt \
	php7.0-mysql \
	php7.0-odbc \
	php7.0-opcache \
	php7.0-pgsql \
	php7.0-phpdbg \
	php7.0-pspell \
	php7.0-readline \
	php7.0-recode \
	php7.0-sqlite3 \
	php7.0-sybase \
	php7.0-tidy \
	php7.0-xmlrpc \
	php7.0-soap \
	php7.0-mbstring \
	php-imagick \
	php7.0-xsl \
	php-ssh2 \
	apache2 \ 
	libapache2-mod-php7.0 \
	mariadb-common mariadb-client \ 
	npm \ 
	composer \ 
	nano \
	curl \ 
	openssl \ 
	imagemagick \ 
	libsodium-dev 
	

ENV LOG_STDOUT **Boolean**
ENV LOG_STDERR **Boolean**
ENV LOG_LEVEL warn
ENV ALLOW_OVERRIDE All
ENV DATE_TIMEZONE UTC
ENV TERM dumb

ADD run-server.sh /tmp/run-server.sh
RUN chmod a+x /tmp/run-server.sh

RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
EXPOSE 443

ENTRYPOINT ["/tmp/run-server.sh"]; 
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
