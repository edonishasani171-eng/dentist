FROM php:8.2-apache

# 1. Install PostgreSQL development libraries and the PHP drivers
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# 2. Enable Apache mod_rewrite for clean URLs and routing
RUN a2enmod rewrite

# 3. Copy all your files from your local directory into the container's web directory
COPY . /var/www/html/

# 4. Set the correct web server permissions so Apache can read your scripts safely
RUN chown -R www-data:www-data /var/www/html

# 5. Expose port 80 so the internet can access the container
EXPOSE 80