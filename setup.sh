#!/bin/bash

echo "勤怠管理アプリのDocker環境をセットアップしています..."

# Laravelプロジェクトを作成
echo "Laravelプロジェクトを作成中..."
docker-compose run --rm app composer create-project --prefer-dist laravel/laravel .

# .envファイルをコピー
echo ".envファイルを設定中..."
cp src/.env.example src/.env

# .envファイルの設定を更新
echo "データベース設定を更新中..."
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/' src/.env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=attendance_db/' src/.env
sed -i 's/DB_USERNAME=root/DB_USERNAME=laravel_user/' src/.env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=laravel_password/' src/.env
sed -i 's|APP_URL=http://localhost|APP_URL=http://localhost:8080|' src/.env

# アプリケーションキーを生成
echo "アプリケーションキーを生成中..."
docker-compose run --rm app php artisan key:generate

# パッケージをインストール
echo "パッケージをインストール中..."
docker-compose run --rm app composer install
docker-compose run --rm app npm install

# TailwindCSSをインストール
echo "TailwindCSSをインストール中..."
docker-compose run --rm app npm install -D tailwindcss postcss autoprefixer @tailwindcss/forms

# TailwindCSS設定ファイルを生成
echo "TailwindCSS設定ファイルを生成中..."
docker-compose run --rm app npx tailwindcss init -p

# TailwindCSS設定ファイルを更新
echo "TailwindCSS設定を更新中..."
cat > src/tailwind.config.js << 'EOF'
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
EOF

# app.cssにTailwindディレクティブを追加
echo "CSSファイルを更新中..."
cat > src/resources/css/app.css << 'EOF'
@tailwind base;
@tailwind components;
@tailwind utilities;

/* カスタムスタイル */
.btn {
    @apply px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

.btn-primary {
    @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
}

.btn-secondary {
    @apply bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2;
}

.btn-success {
    @apply bg-green-600 text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2;
}

.btn-danger {
    @apply bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2;
}

.form-input {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
}

.form-label {
    @apply block text-sm font-medium text-gray-700 mb-1;
}

.card {
    @apply bg-white shadow-md rounded-lg overflow-hidden;
}

.card-header {
    @apply px-6 py-4 bg-gray-50 border-b border-gray-200;
}

.card-body {
    @apply px-6 py-4;
}
EOF

# ビルドプロセスを実行
echo "アセットをビルド中..."
docker-compose run --rm app npm run build

# 権限を設定
echo "権限を設定中..."
docker-compose run --rm app chmod -R 775 storage bootstrap/cache

echo "セットアップが完了しました！"
echo ""
echo "以下のコマンドでアプリケーションを起動してください："
echo "docker-compose up -d"
echo ""
echo "アクセスURL："
echo "- アプリケーション: http://localhost:8080"
echo "- phpMyAdmin: http://localhost:8081"
