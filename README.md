企業の従業員勤怠管理を行うWebアプリケーションです。出退勤の打刻、休憩時間の管理、勤怠修正申請の承認フローなど、勤怠管理に必要な機能を網羅しています。

## 📋 目次

- [環境構築](#環境構築)
- [使用技術](#使用技術)
- [ER図](#er図)
- [URL](#url)
- [動作確認用アカウント](#動作確認用アカウント)
- [機能一覧](#機能一覧)
- [画面構成](#画面構成)

## 🚀 環境構築

### 前提条件
- Docker
- Docker Compose
- Git

### セットアップ手順

1. **リポジトリのクローン**
   ```bash
   git clone https://github.com/hiroakiokamura/attendance-app
   cd attendance_app
   ```

2. **Dockerコンテナのビルドと起動**
   ```bash
   docker-compose up -d --build
   ```

3. **Composerの依存関係をインストール**
   ```bash
   docker-compose exec app composer install
   ```

4. **環境ファイルの設定**
   ```bash
   docker-compose exec app cp .env.example .env
   docker-compose exec app php artisan key:generate
   ```

5. **データベースのマイグレーション実行**
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. **シーディング実行（テストデータの投入）**
   ```bash
   docker-compose exec app php artisan db:seed
   ```

7. **フロントエンドのビルド**
   ```bash
   docker-compose exec app npm install
   docker-compose exec app npm run build
   ```

### 環境確認

以下のURLでアプリケーションにアクセスできます：
- **メインアプリケーション**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **MailHog（メール確認）**: http://localhost:8025

## 💻 使用技術

### バックエンド
- **PHP**: 8.2
- **Laravel**: 12.0
- **Laravel Fortify**: 認証機能
- **MySQL**: 8.0

### フロントエンド
- **Blade Template**: Laravel標準テンプレートエンジン
- **Tailwind CSS**: 3.1.0
- **Alpine.js**: 3.4.2
- **Vite**: 7.0.4（ビルドツール）

### インフラ・開発環境
- **Docker**: コンテナ化
- **Docker Compose**: マルチコンテナ管理
- **Nginx**: Webサーバー
- **MailHog**: メール開発用サーバー
- **phpMyAdmin**: データベース管理

### テスト
- **PHPUnit**: 11.5.3
- **Laravel Pint**: コード整形
- **Faker**: テストデータ生成

## 📊 ER図

![ER図](./public/images/ER/Untitled%20diagram%20_%20Mermaid%20Chart-2025-09-30-055408.png)

## 🌐 URL

### 一般ユーザー
- **会員登録**: http://localhost:8080/register
- **ログイン**: http://localhost:8080/login
- **勤怠登録**: http://localhost:8080/attendance
- **勤怠一覧**: http://localhost:8080/attendance/list
- **申請一覧**: http://localhost:8080/stamp_correction_request

### 管理者
- **管理者ログイン**: http://localhost:8080/admin/login
- **スタッフ一覧**: http://localhost:8080/admin/staff
- **勤怠一覧**: http://localhost:8080/admin/attendance
- **申請一覧**: http://localhost:8080/admin/stamp_correction_request

### その他
- **phpMyAdmin**: http://localhost:8081
- **MailHog**: http://localhost:8025

## 🔐 動作確認用アカウント

### 管理者アカウント
- **メールアドレス**: admin@example.com
- **パスワード**: password

### 一般ユーザーアカウント
- **山田太郎**
  - メールアドレス: yamada@example.com
  - パスワード: password

- **田中花子**
  - メールアドレス: tanaka@example.com
  - パスワード: password

### データベース接続情報
- **ホスト**: localhost:3306
- **データベース名**: attendance_db
- **ユーザー名**: laravel_user
- **パスワード**: laravel_password

## ✨ 機能一覧

### 一般ユーザー機能
- ✅ 会員登録・メール認証
- ✅ ログイン・ログアウト
- ✅ 出勤・退勤打刻
- ✅ 休憩開始・終了打刻（複数回対応）
- ✅ 勤怠一覧表示・月別絞り込み
- ✅ 勤怠詳細表示・編集
- ✅ 勤怠修正申請
- ✅ 申請一覧・ステータス確認

### 管理者機能
- ✅ 管理者専用ログイン
- ✅ スタッフ一覧表示
- ✅ スタッフ別勤怠一覧・CSV出力
- ✅ 勤怠詳細編集
- ✅ 修正申請一覧・承認機能
- ✅ 申請の詳細確認・コメント機能

### システム機能
- ✅ レスポンシブデザイン
- ✅ バリデーション機能
- ✅ CSRF保護
- ✅ タイムゾーン設定（JST）
- ✅ メール送信機能（MailHog）

## 🖼️ 画面構成

### 一般ユーザー画面
1. **会員登録画面** - 新規ユーザー登録
2. **ログイン画面** - 認証
3. **勤怠登録画面** - 出退勤・休憩打刻
4. **勤怠一覧画面** - 月別勤怠履歴
5. **勤怠詳細画面** - 詳細表示・編集
6. **申請一覧画面** - 修正申請の管理

### 管理者画面
1. **管理者ログイン画面** - 管理者認証
2. **スタッフ一覧画面** - 従業員管理
3. **勤怠一覧画面** - 全体勤怠管理
4. **勤怠詳細画面** - 詳細編集
5. **申請一覧画面** - 修正申請管理
6. **申請承認画面** - 申請の承認・却下

## 🧪 テスト実行

```bash
# 全テスト実行
docker-compose exec app php artisan test

# 特定のテストクラス実行
docker-compose exec app php artisan test --filter=AttendanceTest

# カバレッジ付きテスト実行
docker-compose exec app php artisan test --coverage
```

## 🛠️ 開発コマンド

```bash
# Laravelコマンド実行
docker-compose exec app php artisan [command]

# Composerコマンド実行
docker-compose exec app composer [command]

# NPMコマンド実行
docker-compose exec app npm [command]

# データベースリセット
docker-compose exec app php artisan migrate:fresh --seed

# コード整形
docker-compose exec app ./vendor/bin/pint
```