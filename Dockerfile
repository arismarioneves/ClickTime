# Use a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Atualizar dependências e instalar extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos do projeto para o container
COPY . /var/www/html/

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expor a porta 80
EXPOSE 80

# Iniciar Apache
CMD ["apache2-foreground"]
