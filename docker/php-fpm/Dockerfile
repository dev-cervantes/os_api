# Imagem base para o PHP-FPM
FROM php:8.2-fpm

# Instala as dependências necessárias
RUN apt-get update
RUN apt-get -y upgrade

RUN apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
        zlib1g-dev \
        libicu-dev g++ \
        libpq-dev \
        libzip-dev \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure zip \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Copia o código-fonte da API para dentro do container
COPY ../../ /var/www/html/api

# Define o diretório de trabalho
WORKDIR /var/www/html/api

# Instala as dependências da API Laravel
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --no-progress --no-suggest

# Configura as permissões dos arquivos
RUN chown -R www-data:www-data /var/www/html/api/storage
RUN chmod -R 775 /var/www/html/api/storage

# Copia o arquivo de exemplo do ambiente Laravel
COPY ../../.env /var/www/html/api/.env

# Gera a chave do Laravel
RUN composer install

# Exponha a porta do PHP-FPM
EXPOSE 8000

# Inicia o PHP-FPM e o serviço Laravel
CMD cd /var/www/html/api && php artisan cache:clear && php artisan optimize && php artisan serve --host 0.0.0.0 --port 8000
