FROM php:7.0-apache

RUN apt update \
 && apt install youtube-dl -y \
 && rm /var/lib/apt/lists/* /var/log/* -Rf

ADD . /var/www/html

VOLUME ["/var/www/html/files"]
