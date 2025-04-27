FROM composer:2.8.5 AS composer
FROM php:8.3.19-zts-alpine3.21

## Предустановленные константы
ARG DEBIAN_FRONTEND=noninteractive
ARG TZ='Europe/Moscow'
ARG USER_ID=1000
ARG GROUP_ID=1000

## Берем готовый composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

## Чтобы было проще добавлять расширения используем пакет docker-php-extension-installer
RUN curl -sSLf -o /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions parallel xdebug && \
## Трюк, чтобы www-data имела uid/git, как у пользователя и не нужно было постоянно делать chown
    apk add --no-cache shadow && \
    usermod -u ${USER_ID} www-data && groupmod -g ${GROUP_ID} www-data && \
    rm -rf /var/cache/apk/* && \
    mkdir -p /var/www/html && \
    chown -R www-data:www-data /var/www/html

COPY --chown=root:root xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

## Переходим/объявляем в рабочий каталог
WORKDIR /var/www/html

## Поставим пользователя по умолчанию, чтобы при действиях из PHPStorm устанавливались правильные права
USER www-data

CMD ["php", "-a"]
