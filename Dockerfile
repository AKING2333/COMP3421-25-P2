FROM php:8.0-apache

# 安装PHP扩展
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install -j$(nproc) \
    intl \
    pdo_mysql \
    mysqli \
    zip \
    opcache

# 启用Apache模块
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . /var/www/html

# 设置权限
RUN chown -R www-data:www-data /var/www/html