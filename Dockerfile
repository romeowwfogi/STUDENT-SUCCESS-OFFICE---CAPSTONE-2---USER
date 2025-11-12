FROM php:8.2-apache

# Install mysqli and PDO MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules and allow .htaccess overrides
RUN a2enmod rewrite headers \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy your project into Apache’s document root
COPY . /var/www/html/

# Fix permissions (optional, but good practice)
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80