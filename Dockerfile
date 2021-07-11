FROM php:7.4.7-fpm-alpine

# Install app depends
RUN apk add --no-cache \
	tzdata \
	ca-certificates \
	bash \
	nano \
	sudo \
	imagemagick \
	libpq

# Add git
#RUN apk add --no-cache \
#	git \
#	openssh

# Install php exetensions
RUN apk add --no-cache --virtual '.docker-php-ext-enable-deps' $PHPIZE_DEPS libxml2-dev postgresql-dev \
    && docker-php-ext-install opcache exif pdo_pgsql xml pcntl \
    && pecl install xattr inotify \
    && docker-php-ext-enable xattr inotify \
    && ln -fs /usr/local/bin/php /usr/bin/php \
    && docker-php-source delete

# Add composer
#ADD https://getcomposer.org/download/2.0.9/composer.phar /usr/bin/composer
#RUN chmod +x /usr/bin/composer

# php settings
ADD ./.docker/app/php.ini /usr/local/etc/php/php.ini

# Copy App and Settings
ADD . /var/www/
RUN chown -R www-data:www-data /var/www

WORKDIR /var/www

ENTRYPOINT ["/init"]

EXPOSE 80
