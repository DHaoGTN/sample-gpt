FROM php:8.1.23-fpm-alpine

RUN set -eux; \
    \
    apk add --update --no-cache --virtual .build-deps \
        freetype-dev \
        gmp-dev \
        icu-dev \
        libintl \
        libjpeg-turbo-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        linux-headers \
        postgresql-dev \
        $PHPIZE_DEPS \
    ; \
    docker-php-ext-configure gd \
        --with-freetype=/usr/include/ \
        --with-jpeg=/usr/include/ \
    ; \
    docker-php-ext-install -j"$(getconf _NPROCESSORS_ONLN)" \
        bcmath \
        gd \
        gmp \
        intl \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        pcntl \
        sockets \
        zip \
    ;

COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
ENV PATH $PATH:/composer/vendor/bin


# FROM php:8.0.3-fpm

# RUN docker-php-ext-install pdo pdo_mysql

# RUN apt-get update

# # Install useful tools
# RUN apt-get -y install apt-utils nano wget dialog vim

# # Install important libraries
# RUN echo "\e[1;33mInstall important libraries\e[0m"
# RUN apt-get -y install --fix-missing \
#     apt-utils \
#     build-essential \
#     git \
#     curl \
#     libcurl4 \
#     libcurl4-openssl-dev \
#     zlib1g-dev \
#     libzip-dev \
#     zip \
#     libbz2-dev \
#     locales \
#     libmcrypt-dev \
#     libicu-dev \
#     libonig-dev \
#     libxml2-dev
    
# RUN echo "\e[1;33mInstall important docker dependencies\e[0m"
# RUN docker-php-ext-install \
#     exif \
#     pcntl \
#     bcmath \
#     ctype \
#     curl \
#     iconv \
#     xml \
#     soap \
#     pcntl \
#     mbstring \
#     tokenizer \
#     bz2 \
#     zip \
#     intl

# # Install Postgre PDO
# RUN apt-get install -y libpq-dev \
#     && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
#     && docker-php-ext-install pdo pdo_pgsql pgsql

# COPY --from=composer /usr/bin/composer /usr/bin/composer
# ENV COMPOSER_ALLOW_SUPERUSER 1
# ENV COMPOSER_HOME /composer
# ENV PATH $PATH:/composer/vendor/bin
