# =====================================================
# TÜRK FİLMLERİ PLATFORMU - DOCKERFILE
# PHP 8.2 + Apache + MySQL PDO
# Render.com deployment için optimize edilmiş
# =====================================================

FROM php:8.2-apache

# Sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Apache mod_rewrite aktif et
RUN a2enmod rewrite

# Apache yapılandırması - DocumentRoot ayarla
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# AllowOverride All ayarla
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# PHP ayarları
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/uploads.ini

# Proje dosyalarını kopyala
COPY . /var/www/html/

# Upload dizinleri oluştur ve izinleri ayarla
RUN mkdir -p /var/www/html/uploads/posters \
    /var/www/html/uploads/actors \
    /var/www/html/uploads/gallery \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads

# Render PORT environment variable desteği
# Render $PORT değişkeni ile port belirler
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-available/000-default.conf

# Varsayılan port
ENV PORT=10000

EXPOSE ${PORT}

# Apache'yi foreground'da çalıştır
CMD ["apache2-foreground"]
