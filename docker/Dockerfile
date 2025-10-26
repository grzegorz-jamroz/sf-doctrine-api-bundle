FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    e2fsprogs \
    sudo

## Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN docker-php-ext-install pdo_mysql

COPY ./docker/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini

COPY ./docker/xdebug-toggle.sh /usr/local/bin/xdebug
RUN chmod +x /usr/local/bin/xdebug

RUN xdebug off

WORKDIR /app

COPY ./.env.example /app/.env
RUN sed -i 's#^DATABASE_URL=.*#DATABASE_URL="mysql://db:db@db:3306/db"#' /app/.env
COPY ./composer.json /app/composer.json

RUN composer install

RUN sudo chown -R 1000:1000 /app
RUN sudo mkdir -p /opt/phpstorm-coverage && sudo chown 1000:1000 /opt/phpstorm-coverage

CMD ["tail", "-f", "/dev/null"]
