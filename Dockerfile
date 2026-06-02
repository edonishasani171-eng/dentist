FROM php:8.2-apache

# Install required PostgreSQL extensions for PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite for clean URL routing
RUN a2enmod rewrite

# Copy all your frontend and backend files into the container
COPY . /var/www/html/

# Tell Apache that your actual website home folder lives inside /var/www/html/api
ENV APACHE_DOCUMENT_ROOT /var/www/html/api
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Ensure Apache has the correct permissions to read your files
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80