# syntax = docker/dockerfile:1

ARG DEBIAN_VERSION=bookworm
ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-apache-${DEBIAN_VERSION} as base

ARG COMPOSER_VERSION=2.7.1
ENV APACHE_DOCUMENT_ROOT /app/public

# The PHP app lives here
WORKDIR /app

# Copy over the application code
COPY . .

# Install Node.js
RUN apt-get update && apt install -y curl
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash \
    && exec bash \
    && nvm install --lts \
    && rm -rvf /var/cache/apt/archives

# Install PHP's dependencies
COPY --from=composer:2.2.7 /usr/bin/composer /usr/bin/
RUN /usr/bin/composer install \
      --no-dev --no-ansi --no-plugins --no-progress --no-scripts \
      --classmap-authoritative --no-interaction \
      --quiet

FROM base as final

# The PHP app lives here
WORKDIR /app

# Copy over the application code
COPY . .

# Change the document root
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
