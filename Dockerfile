# Build stage for frontend assets
FROM node:20-bookworm AS node-builder
WORKDIR /var/www
COPY package.json package-lock.json vite.config.js postcss.config.js tailwind.config.js ./
COPY resources resources
RUN npm ci
RUN npm run build

# Runtime stage: Nginx + PHP-FPM
FROM php:8.3-fpm-bookworm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    git \
    curl \
    unzip \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libxml2-dev \
    ca-certificates \
    zip \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip gd intl bcmath pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* /tmp/pear



# Optimize PHP-FPM pool settings (increase workers from default 5 to 20)
RUN sed -i 's/pm.max_children = 5/pm.max_children = 25/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/pm.start_servers = 2/pm.start_servers = 5/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 3/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 8/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;pm.max_requests = 500/pm.max_requests = 1000/' /usr/local/etc/php-fpm.d/www.conf \
    && echo "request_terminate_timeout = 120" >> /usr/local/etc/php-fpm.d/www.conf

# Production PHP & OPcache tuning
RUN printf "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=10000\n\
opcache.revalidate_freq=0\n\
opcache.validate_timestamps=0\n\
memory_limit=512M\n\
upload_max_filesize=32M\n\
post_max_size=32M\n\
max_execution_time=120\n" > /usr/local/etc/php/conf.d/production.ini


# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application source
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install Node modules (Vite/frontend build tools only — PDF now uses DomPDF, no Chrome needed)
RUN npm install --omit=dev

# Copy built frontend assets

COPY --from=node-builder /var/www/public/build public/build


# Copy Nginx config (PORT placeholder will be replaced at runtime by entrypoint)
COPY nginx.conf /etc/nginx/sites-available/default

# Ensure required Laravel storage subdirectories exist
RUN mkdir -p /var/www/storage/framework/cache \
             /var/www/storage/framework/sessions \
             /var/www/storage/framework/views \
             /var/www/storage/logs

# Ensure Laravel storage/cache permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
