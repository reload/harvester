FROM composer/composer

COPY etc/apt/sources.list.d/stable.list /etc/apt/sources.list.d/stable.list
COPY etc/apt/sources.list.d/testing.list /etc/apt/sources.list.d/testing.list

RUN apt-get update
RUN apt-get install php7.0-bcmath php7.0-intl -y

COPY usr/local/etc/php/conf.d/docker-php-ext-bcmath.ini /usr/local/etc/php/conf.d/docker-php-ext-bcmath.ini
COPY usr/local/etc/php/conf.d/docker-php-ext-intl.ini /usr/local/etc/php/conf.d/docker-php-ext-intl.ini
