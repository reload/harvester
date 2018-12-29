FROM php:5-cli-alpine

ARG COMPOSER_VER=1.8.0

# Add build dependencies.
RUN apk upgrade --update \
    && apk add --no-cache --virtual .build-deps \
    gcc g++ make \
    autoconf \
    gettext-dev \
    icu-dev \
    libmcrypt-dev \
    libintl \
    openssl-dev \
    pcre-dev

# Add runtime dependencies.
RUN apk --no-cache add \
    git \
    gettext \
    icu \
    intltool \
    libmcrypt \
    re2c

# Install required extensions.
RUN docker-php-ext-configure bcmath \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) bcmath intl

# Copy PHP settings
COPY php-timezone.ini /usr/local/etc/php/conf.d/

# Remove build dependencies.
RUN apk del --purge .build-deps

# Install composer using the installer.
# We compare the downloaded installer with a hash from a second source to reduce
# the risk of downloading a compromised installer.
RUN curl --silent -o /tmp/composer-installer https://getcomposer.org/installer && \
  curl --silent -o /tmp/composer-installer.sig https://composer.github.io/installer.sig &&  \
  php -r "if (hash('SHA384', file_get_contents('/tmp/composer-installer')) !== trim(file_get_contents('/tmp/composer-installer.sig'))) { unlink('/tmp/composer-installer'); echo 'Invalid installer' . PHP_EOL; exit(1); }" && \
  php /tmp/composer-installer --version=$COMPOSER_VER --filename=composer --install-dir=/usr/local/bin --no-ansi && \
  php -r "unlink('/tmp/composer-installer');" && \
  php -r "unlink('/tmp/composer-installer.sig');"
RUN composer global require hirak/prestissimo

WORKDIR "/app"
ENTRYPOINT ["/usr/local/bin/composer"]