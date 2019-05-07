FROM composer:1 AS composer

FROM php:7.0-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

#Installing and enabling features and PHP extension needed
RUN apt-get update -y \
  && apt-get install -y libxml2-dev libmcrypt-dev git unzip \
  && apt-get clean -y \
  && docker-php-ext-install soap mcrypt