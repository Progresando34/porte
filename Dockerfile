# Usar imagen oficial de PHP con Composer y extensiones necesarias
FROM php:8.2-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código
WORKDIR /var/www/html
COPY . .

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader

# Copiar archivo de entorno
COPY .env.example .env
RUN php artisan key:generate

# Exponer puerto
EXPOSE 9000

# Comando por defecto
CMD ["php-fpm"]
