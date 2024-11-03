# Utiliser une image PHP avec les extensions nécessaires pour Laravel
FROM php:8.3-apache

# Installer les dépendances nécessaires pour Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier les fichiers de l'application dans le répertoire de l'image
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances Laravel
RUN composer install --optimize-autoloader --no-dev

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Exposer le port 80 pour accéder à l'application
EXPOSE 80

# Lancer Apache en mode foreground
CMD ["apache2-foreground"]
