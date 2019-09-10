#!/bin/bash

function exportBoolean {
    if [ "${!1}" = "**Boolean**" ]; then
            export ${1}=''
    else 
            export ${1}='Yes.'
    fi
}

exportBoolean LOG_STDOUT
exportBoolean LOG_STDERR

if [ $LOG_STDERR ]; then
    /bin/ln -sf /dev/stderr /var/log/apache2/error.log
else
	LOG_STDERR='No.'
fi

if [ $ALLOW_OVERRIDE == 'All' ]; then
    /bin/sed -i 's/AllowOverride\ None/AllowOverride\ All/g' /etc/apache2/apache2.conf
fi

if [ $LOG_LEVEL != 'warn' ]; then
    /bin/sed -i "s/LogLevel\ warn/LogLevel\ ${LOG_LEVEL}/g" /etc/apache2/apache2.conf
fi

/bin/sed -i "s/Options\ Indexes/Options/" /etc/apache2/apache2.conf

# enable php short tags:
/bin/sed -i "s/short_open_tag\ \=\ Off/short_open_tag\ \=\ On/g" /etc/php/7.0/apache2/php.ini

# stdout server info:
if [ $LOG_STDOUT ]; then
    /bin/ln -sf /dev/stdout /var/log/apache2/access.log
fi

# Set PHP timezone
/bin/sed -i "s/\;date\.timezone\ \=/date\.timezone\ \=\ ${DATE_TIMEZONE}/g" /etc/php/7.0/apache2/php.ini
/bin/sed -i "s/\;date\.timezone\ \=/date\.timezone\ \=\ ${DATE_TIMEZONE}/g" /etc/php/7.0/cli/php.ini
/bin/sed -i "s/bind\ 127\.0\.0\.1\ \:\:1/bind\ 127\.0\.0\.1/" /etc/redis/redis.conf


# MySQL
if [ $MYSQL_DATABASE ] && [ $MYSQL_USER ] && [ $MYSQL_PASSWORD ] && [ $MYSQL_HOST ]; then

/usr/sbin/postfix start

echo "priprava databaze"
mysql -h $MYSQL_HOST -u root -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cat /docker-entrypoint-initdb.d/*.sql | mysql -h $MYSQL_HOST -u root -p$MYSQL_ROOT_PASSWORD $MYSQL_DATABASE
mysql -h $MYSQL_HOST -u root -p$MYSQL_ROOT_PASSWORD -e "CREATE USER IF NOT EXISTS ${MYSQL_USER}@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';"
mysql -h $MYSQL_HOST -u root -p$MYSQL_ROOT_PASSWORD -e "GRANT ALL PRIVILEGES ON ${MYSQL_DATABASE}.* TO '${MYSQL_USER}'@'%';"
mysql -h $MYSQL_HOST -u root -p$MYSQL_ROOT_PASSWORD -e "FLUSH PRIVILEGES;"
echo "databaze ok"

fi
# Run Sendmail
line=$(head -n 1 /etc/hosts)
line2=$(echo $line | awk '{print $2}')
echo "$line $line2.local.cz" >> /etc/hosts

if [ $SASS_INPUT ] && [ $SASS_OUTPUT ]; then
	sass --watch --no-error-css -q --style=compressed $SASS_INPUT:$SASS_OUTPUT &
fi

/etc/init.d/sendmail start
/etc/init.d/redis-server start
service cron start

# Run Composer
echo "application update start"
if [ $COMPOSER_INTERACTION ]; then
  composer update --ansi
else
  composer update --no-interaction --quiet
fi
echo "application update finished"

# Run Apache:
if [ $LOG_LEVEL == 'debug' ]; then
    /usr/sbin/apachectl -DFOREGROUND -k start -e debug
else
    &>/dev/null /usr/sbin/apachectl -DFOREGROUND -k start
fi