ARG PHP_VERSION=8.2
ARG NODE_VERSION=20

FROM node:${NODE_VERSION}-alpine AS node-build
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm ci
RUN npm run build

FROM php:${PHP_VERSION}-fpm-alpine AS php-base
WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        postgresql-libs \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        postgresql-dev \
        oniguruma-dev \
        git \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

FROM php-base AS composer-deps
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

FROM php-base AS app
WORKDIR /var/www/html

COPY . .
COPY --from=composer-deps /var/www/html/vendor ./vendor
COPY --from=node-build /app/public/build ./public/build

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

RUN php artisan package:discover --ansi

COPY deploy/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

FROM nginx:1.27-alpine AS nginx
WORKDIR /var/www/html
COPY --from=app /var/www/html/public ./public
COPY deploy/nginx.conf /etc/nginx/conf.d/default.conf
