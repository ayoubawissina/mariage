# Étape 1 : Utilise PHP avec Apache
FROM php:8.2-apache

# Étape 2 : Installe les extensions nécessaires
RUN docker-php-ext-install mysqli

# Étape 3 : Installe Composer (via multi-stage)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Étape 4 : Définit le dossier de travail
WORKDIR /var/www/html/

# Étape 5 : Copie le projet dans le conteneur
COPY . .

# Étape 6 : Installe les dépendances avec Composer
RUN composer install --no-interaction --no-ansi --no-scripts --prefer-dist --optimize-autoloader

# Étape 7 : Expose le port Apache
EXPOSE 80
