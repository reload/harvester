FROM composer/composer

COPY etc/apt/sources.list.d/stable.list /etc/apt/sources.list.d/stable.list
COPY etc/apt/sources.list.d/testing.list /etc/apt/sources.list.d/testing.list

RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 04EE7237B7D453EC
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys EF0F382A1A7B6500

RUN export DEBIAN_FRONTEND=noninteractive && \
    apt-get update && \
    apt-get remove -y binutils && \
    apt-get install -y git php7.0-bcmath php7.0-intl && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY usr/local/etc/php/conf.d/docker-php-ext-bcmath.ini /usr/local/etc/php/conf.d/docker-php-ext-bcmath.ini
COPY usr/local/etc/php/conf.d/docker-php-ext-intl.ini /usr/local/etc/php/conf.d/docker-php-ext-intl.ini
