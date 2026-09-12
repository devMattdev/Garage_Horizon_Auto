# syntax=docker/dockerfile:1
#
# Garage Horizon Auto — image PHP-FPM
#
# Usage :
#   docker build --target dev  -t garage-horizon-auto-php:dev  -f docker/php/Dockerfile .
#   docker build --target prod -t garage-horizon-auto-php:prod -f docker/php/Dockerfile .
#
# Le stage "prod" est celui construit et poussé par la CI (voir .github/workflows/ci.yaml
# et docs/deploiement.md). Le stage "dev" est utilisé par docker-compose.yaml en local.

ARG PHP_VERSION=8.4

# ---------------------------------------------------------------------------
# Stage de base : extensions systeme et extensions PHP communes aux deux cibles
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine AS base

# Dependances systeme necessaires aux extensions PHP (intl, zip, gd, pdo_mysql)
RUN apk add --no-cache \
        icu-data-full \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        libwebp \
        && apk add --no-cache --virtual .build-deps \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        opcache \
        zip \
        gd \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps

# APCu pour le cache de metadonnees/requetes Doctrine et le cache systeme Symfony
# (back.md section 3 et 12) : desactive en CLI par defaut (apc.enable_cli=0), Symfony
# retombe alors automatiquement sur un cache fichiers pour les commandes console.
COPY docker/php/conf.d/apcu.ini /usr/local/etc/php/conf.d/zz-apcu.ini

WORKDIR /var/www/html

# Extension du systeme de fichiers Symfony : var/ doit etre inscriptible par www-data
RUN mkdir -p var/cache var/log var/share \
    && chown -R www-data:www-data var

EXPOSE 9000

# ---------------------------------------------------------------------------
# Stage "dev" : xdebug + configuration opcache adaptee au developpement
#   (validate_timestamps=1, pas de precompilation, Composer avec les paquets dev)
# ---------------------------------------------------------------------------
FROM base AS dev

RUN apk add --no-cache --virtual .build-deps-xdebug $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps-xdebug

COPY docker/php/conf.d/opcache-dev.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Le code applicatif est monte en volume par docker-compose.yaml en developpement ;
# rien n'est copie ici pour laisser bind-mount et hot-reload fonctionner.

CMD ["php-fpm"]

# ---------------------------------------------------------------------------
# Stage "vendor" : installation des dependances de production (cache Docker friendly)
# ---------------------------------------------------------------------------
FROM base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock symfony.lock ./

# APP_ENV=prod desactive les scripts qui ont besoin du reste du code source
# (auto-scripts type cache:clear) pendant l'installation des dependances.
ENV APP_ENV=prod
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --no-interaction \
        --no-progress

# ---------------------------------------------------------------------------
# Stage "prod" : image finale, code applicatif + autoloader optimise + opcache figee
# ---------------------------------------------------------------------------
FROM base AS prod

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY docker/php/conf.d/opcache-prod.ini /usr/local/etc/php/conf.d/zz-opcache.ini

COPY --from=vendor /usr/bin/composer /usr/bin/composer
COPY --from=vendor /var/www/html/vendor ./vendor

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && composer dump-env prod \
    && php bin/console importmap:install --env=prod --no-debug \
    && php bin/console asset-map:compile --env=prod --no-debug \
    && rm -rf var/cache/* \
    && chown -R www-data:www-data var public/assets \
    && rm -f /usr/bin/composer

USER www-data

CMD ["php-fpm"]
