FROM dunglas/frankenphp:1-php8.5

WORKDIR /var/www

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

ENTRYPOINT ["/bin/sh", "-c", "composer install --no-interaction --prefer-dist && exec docker-php-entrypoint \"$@\"", "--"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
