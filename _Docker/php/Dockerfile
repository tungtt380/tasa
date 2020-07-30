FROM php:7.2-fpm
MAINTAINER PHP72fpm

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install mbstring

#git,npm,vimインストール
RUN apt-get update
RUN apt-get install -y git
RUN apt-get install -y npm
RUN apt-get install -y vim

RUN apt-get install -y libmcrypt-dev
RUN apt-get install -y zip unzip

RUN apt-get install libmagickwand-dev -y --no-install-recommends
RUN pecl install imagick
# add mcrypt ext from pecl
RUN pecl install mcrypt-1.0.3
RUN docker-php-ext-enable imagick
# enable mcrypt ext
RUN docker-php-ext-enable mcrypt

#composerインストール
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
