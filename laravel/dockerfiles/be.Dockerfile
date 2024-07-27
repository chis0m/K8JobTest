FROM  cl0ud/php82-larabase

# Install the composer packages using www-data user
COPY docker/php/php.ini /usr/local/etc/php/conf.d/docker-php-memlimit.ini
WORKDIR /app
RUN chown www-data:www-data /app
COPY --chown=www-data:www-data . .
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
USER www-data
RUN cp .env.example .env
RUN composer install --prefer-dist --no-dev

USER root
RUN apk add --no-progress --quiet --no-cache \
    nginx \
    supervisor \
    mysql-client \
    mariadb-connector-c

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
# Apply the required changes to run nginx as www-data user
RUN chown -R www-data:www-data /run/nginx /var/lib/nginx /var/log/nginx && \
    sed -i '/user nginx;/d' /etc/nginx/nginx.conf
# Switch to www-user
USER www-data
EXPOSE 8000
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
# [END APP STAGE]
