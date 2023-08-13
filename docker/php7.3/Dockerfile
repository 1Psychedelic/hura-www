FROM php:7.3-apache

#RUN apt-get update && apt-get install -y libmcrypt-dev \
#    mysql-client libmagickwand-dev --no-install-recommends \
#    && pecl install imagick \
#    && docker-php-ext-enable imagick \
#&& docker-php-ext-install mcrypt pdo_mysql

RUN apt-get update
RUN apt-get install -y \
    git \
    g++ \
    libzip-dev

RUN apt-get install -y sendmail libpng-dev

RUN apt-get install -y zlib1g-dev

RUN docker-php-ext-install mbstring

RUN docker-php-ext-install zip

RUN docker-php-ext-install gd

# Install php extensions
RUN docker-php-ext-install mysqli pdo_mysql
#RUN docker-php-ext-install opcache
#RUN docker-php-ext-install intl
#RUN docker-php-ext-configure gd --with-jpeg \
#    && docker-php-ext-install -j$(nproc) gd

# Config Apache2
RUN a2enmod rewrite

# Installing composer and prestissimo globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1
#RUN composer global require hirak/prestissimo --prefer-dist --no-progress --no-suggest --classmap-authoritative --no-plugins --no-scripts
RUN composer self-update
