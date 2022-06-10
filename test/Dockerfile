FROM php:7.4.4-apache

RUN apt update && \
    apt install -y \
        git \
        libzip-dev \
        mc && \
    curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    docker-php-ext-install zip && \
	docker-php-ext-enable zip && \
    docker-php-ext-install mysqli && \
	docker-php-ext-enable mysqli
    
COPY ./composer.json ./composer.json
#COPY ./composer.lock ./composer.lock

RUN composer update && \
    composer install
	
COPY ./ ./