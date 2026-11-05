# Runs the MCP server on Apache. Configure with env vars:
# APP_KEY, AKAUNTING_BASE_URL, AKAUNTING_COMPANY_ID (optional), APP_URL.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize

FROM php:8.3-apache
RUN a2enmod rewrite \
 && sed -ri 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
 && printf '<Directory /var/www/html/public>\n  AllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr SESSION_DRIVER=array CACHE_STORE=array
EXPOSE 80
