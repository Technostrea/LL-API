# Étape 1 : Construction de l'application Laravel
FROM php:8.3-fpm AS build

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev  # Ajout de la bibliothèque PostgreSQL

# Installer les extensions PHP requises
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd  # Remplacement par pdo_pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www

# Copier le code source de l'application Laravel
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Générer la clé d'application Laravel et exécuter les migrations
RUN php artisan key:generate
RUN php artisan migrate --force

# Optimiser Laravel pour la production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache


# Étape 2 : Serveur Nginx (Alpine)
FROM nginx:alpine AS production

# Copier la configuration Nginx
COPY ./nginx.conf /etc/nginx/conf.d

# Copier les fichiers Laravel construits
COPY --from=build /var/www /var/www

# Assurer les permissions pour le stockage et les caches
RUN chown -R nginx:nginx /var/www \
    && chmod -R 755 /var/www/storage

# Exposer le port Nginx
EXPOSE 80

# Commande pour démarrer Nginx
CMD ["nginx", "-g", "daemon off;"]
