# FrankenPHP Dockerfile for Padi REST API
# Multi-stage build for optimized production image

# Stage 1: Base image with FrankenPHP
# Stage 1: Base image with FrankenPHP
FROM dunglas/frankenphp AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Stage 2: Development image
FROM base AS development

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies (with dev dependencies)
RUN composer install --no-scripts --no-autoloader

# Copy application files
COPY . .

# Generate optimized autoloader AFTER copying all files
RUN composer dump-autoload --optimize

# Copy both Caddyfile versions
COPY Caddyfile.standard /etc/caddy/Caddyfile.standard
COPY Caddyfile.worker /etc/caddy/Caddyfile.worker
COPY Caddyfile /etc/caddy/Caddyfile

# Create storage directories and set permissions
RUN mkdir -p storage/cache storage/cache/ratelimit storage/logs \
    && chmod -R 775 storage \
    && chown -R www-data:www-data storage

# Expose port
EXPOSE 8085

# Start Caddy with custom Caddyfile
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

# Stage 3: Production image
FROM base AS production

# Set production environment
ENV APP_ENV=production
ENV APP_DEBUG=false

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies (without dev dependencies)
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# Copy application files
COPY . .

# Generate optimized autoloader for production
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Create storage directories and set permissions
RUN mkdir -p storage/cache storage/cache/ratelimit storage/logs \
    && chmod -R 775 storage \
    && chown -R www-data:www-data storage

# Remove unnecessary files
RUN rm -rf \
    tests \
    .git \
    .gitignore \
    .env.example \
    README.md \
    docker-compose.yml

# Expose port
EXPOSE 8085

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8085/ || exit 1

# Start Caddy with custom Caddyfile
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
