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

# Zależności PHP instaluj w uruchomionym kontenerze (bind mount .:/var/www):
#   docker compose exec php composer install
# Nie rób `composer install` w buildzie — post-install wymaga bin/console i reszty aplikacji,
# a volume i tak nadpisuje /var/www zawartością z hosta.
