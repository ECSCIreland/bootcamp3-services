FROM php:7.4-apache

COPY --chown=www-data src/www /app/www
COPY --chown=www-data src/internal /app/internal


# Downloaded from http://htmlpurifier.org/releases/htmlpurifier-4.15.0-standalone.tar.gz
ADD --chown=www-data htmlpurifier-4.15.0-standalone.tar.gz /tmp/
RUN mv /tmp/htmlpurifier-4.15.0-standalone/* /app/www

# Pregenerated with composer install
ADD --chown=www-data vendor.tar.gz /app/www

ENV APACHE_DOCUMENT_ROOT /app


RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}/www!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN cp /etc/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/internal.conf
RUN sed -ri -e 's!/www!/internal!g' /etc/apache2/sites-enabled/internal.conf
RUN sed -ri -e 's!VirtualHost \*:80!VirtualHost \*:3000!g' /etc/apache2/sites-enabled/internal.conf
RUN echo Listen 3000 >> /etc/apache2/ports.conf

RUN mkdir /app/policies
RUN mkdir /app/ioc
RUN chown -R www-data /app
