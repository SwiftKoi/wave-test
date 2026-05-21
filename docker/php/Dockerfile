FROM php:8.4-apache


ARG UID=1000
ARG GID=1000

RUN groupmod -o -g ${GID} www-data \
    && usermod -o -u ${UID} -g ${GID} www-data


RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli zip gd \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

WORKDIR /var/www/html

EXPOSE 80
