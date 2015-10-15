FROM phusion/baseimage:0.9.17

# Need git for cloning Harvester, cron for background tasks and ssmtp
# to avoid having cron pull in a full blown MTA like Exim, and apache,
# php and extensions
RUN export DEBIAN_FRONTEND=noninteractive;
    apt-get update && \
    apt-get -q install -y ssmtp git apache2 php5 php5-sqlite php5-intl php5-curl

# REST service app requires mod_rewrite.
RUN a2enmod rewrite

# Composer for building Harvester.
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer;

# Setup defaults for variables.
ENV HARVESTER_SECRET ThisTokenIsNotSoSecretChangeIt
ENV HARVESTER_HOURS_PER_DAY 7.5
ENV HARVESTER_BILLABILITY_GOAL 75
ENV HARVESTER_HARVEST_USER harvest_account_email@email.com
ENV HARVESTER_HARVEST_PASSWORD secretpassword
ENV HARVESTER_HARVEST_ACCOUNT harvestapp_account_name
ENV SYMFONY_ENV prod

# Fix AllowOverride All for /var/www/html
COPY apache2.conf /etc/apache2/apache2.conf

# Add our crontab.
COPY crontab /etc/cron.d/harvester

# Script for setting up harvester.
RUN mkdir -p /etc/my_init.d
COPY harvester /etc/my_init.d/

# Make runit start apache.
RUN mkdir /etc/service/apache2
COPY apache2.service /etc/service/apache2/run

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /harvester
