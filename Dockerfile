FROM php:8.4-apache

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        intl \
        bcmath \
        opcache \
    && a2enmod rewrite headers \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && echo "mysqli.default_socket = /var/run/mysqld/mysqld.sock" > /usr/local/etc/php/conf.d/socket.ini \
    && echo "pdo_mysql.default_socket = /var/run/mysqld/mysqld.sock" >> /usr/local/etc/php/conf.d/socket.ini \
    && ln -sf /var/run/mysqld/mysqld.sock /tmp/mysql.sock \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application code into container
COPY . /var/www/html

# Ensure storage directory exists with proper permissions
RUN mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80

CMD ["apache2-foreground"]
