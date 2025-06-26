# Utilise une image officielle PHP avec Apache
FROM php:8.2-apache

# Active les extensions nécessaires (si besoin)
RUN docker-php-ext-install mysqli

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier les fichiers de ton projet dans le dossier Apache
COPY . /var/www/html/

# Lancer composer install si composer.json existe
WORKDIR /var/www/html/
RUN if [ -f composer.json ]; then composer install; fi

# Rendre Apache accessible depuis l’extérieur
EXPOSE 80
