FROM php:7.4-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY ./public /var/www/html/
COPY ./config /var/www/config/
COPY ./includes /var/www/includes/
COPY ./sql /var/www/sql/

# Set permissions (insecure for demo purposes)
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
