FROM php:7.3-apache

# Install libraries
RUN apt-get update \
 && apt-get install -y \
      cron \
      libfreetype6-dev \
      libicu-dev \
      libjpeg-dev \
      libpng-dev \
      libxml2-dev \
      libxslt1-dev \
      libzip-dev \
      rsyslog \
      rsyslog-gnutls \
      unzip \
      msmtp \
 && docker-php-ext-configure gd \
      --with-freetype-dir=/usr/include/ \
      --with-jpeg-dir=/usr/include/ \
 && docker-php-ext-install \
      bcmath \
      gd \
      intl \
      pdo_mysql \
      soap \
      xsl \
      zip \
      sockets \
      opcache

# Relax SSL Ciphers security
RUN sed --in-place --expression 's:\(^CipherString = DEFAULT@SECLEVEL=\)2$:\11:' /etc/ssl/openssl.cnf

# Config rsyslog
RUN sed -i '/imklog/s/^/#/' /etc/rsyslog.conf
COPY CI_CD/gsoc_ca.crt /root
COPY CI_CD/gsoc_magento_log.conf /etc/rsyslog.d/
COPY CI_CD/entrypoint.sh /root/entrypoint.sh

# Turn on mod_rewrite
RUN a2enmod rewrite

# Set PHP memory limits
RUN echo "memory_limit=4096M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Config opcache
COPY CI_CD/php/opcache.ini /usr/local/etc/php/conf.d/

# Install MTA & Set PHP sendmail php path
RUN curl -sSLO https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 \
  && chmod +x mhsendmail_linux_amd64 \
  && mv mhsendmail_linux_amd64 /usr/local/bin/mhsendmail \
  && echo 'sendmail_path = "/usr/local/bin/mhsendmail --smtp-addr=postfix.postfix.svc.cluster.local:587"' > /usr/local/etc/php/conf.d/mail-mta.ini

# Copy auth file for access to composer repositories
COPY --chown=www-data CI_CD/auth.json .

# Install composer
RUN curl -sS https://getcomposer.org/installer | \
 php -- --version=1.10.19 --install-dir=/usr/local/bin --filename=composer \
 && mkdir /var/www/.composer \
 && chown -R www-data:www-data /var/www/.composer

# Copy sources
COPY --chown=www-data . .
RUN rm -R CI_CD

USER www-data
# Install prestissimo globaly
RUN composer global require hirak/prestissimo
# Run install without autoloader
RUN composer install --no-interaction --no-dev --no-autoloader --prefer-dist
# Run dump autoload to optimize the classes load
RUN composer dump-autoload --no-interaction --no-dev --optimize

# Execution permission for the bin/magento
RUN chmod u+x bin/magento

# Compile magento
RUN bin/magento setup:di:compile

# Define entrypoint
USER root
RUN chmod 0744 /root/entrypoint.sh
ENTRYPOINT /root/entrypoint.sh
