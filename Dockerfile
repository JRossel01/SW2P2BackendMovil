FROM php:8.2-fpm-alpine

# Establecer directorio de trabajo
WORKDIR /var/www

# Instalar dependencias del sistema necesarias para Laravel + MongoDB
RUN apk update && apk add --no-cache \
    git \
    curl \
    unzip \
    libpng \
    libpng-dev \
    libxml2 \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    openssl-dev \
    zlib-dev \
    autoconf \
    gcc \
    g++ \
    make \
    && docker-php-ext-install \
        zip \
        pdo \
        pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar proyecto Laravel
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Asignar permisos necesarios
RUN chmod -R 755 storage bootstrap/cache

# Exponer puerto
EXPOSE 8000

# Comando por defecto
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
