# Usar imagen oficial de PHP con FPM y Composer
FROM php:8.2-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear directorio de trabajo y copiar proyecto
WORKDIR /var/www/html
COPY . .

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader

# Copiar archivo .env y generar key
COPY .env.example .env
RUN php artisan key:generate

# Exponer puerto para Laravel
EXPOSE 9000

# Comando para iniciar Laravel
CMD ["php-fpm"]
