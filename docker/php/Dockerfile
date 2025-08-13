FROM php:8.2-fpm

# 作業ディレクトリを設定
WORKDIR /var/www/html

# システムの依存関係をインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# PHP拡張をインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ユーザーを作成
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# ファイルの所有権を変更
COPY --chown=www:www . /var/www/html

# ユーザーを切り替え
USER www

# PHP-FPMを起動
EXPOSE 9000
CMD ["php-fpm"]
