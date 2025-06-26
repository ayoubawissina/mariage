# Étape 1 : Utilise PHP avec Apache
FROM php:8.2-apache

# Étape 2 : Installe les extensions nécessaires + outils
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install mysqli

# Étape 3 : Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Étape 4 : Copie le projet dans le conteneur
COPY . /var/www/html/

# Étape 5 : Place-toi dans le dossier du projet
WORKDIR /var/www/html/

# Étape 6 : Installe les dépendances
RUN composer install --no-interaction --no-ansi --no-progress --prefer-dist

# Étape 7 : Expose le port Apache
EXPOSE 80
