# 勤怠管理アプリ

Laravel + Docker環境で構築された勤怠管理アプリケーションです。

## ✨ 機能一覧

### 一般ユーザー機能
- **PG01**: 会員登録画面 (`/register`)
- **PG02**: ログイン画面 (`/login`)
- **PG03**: 勤怠登録画面 (`/attendance`) - リアルタイム時計、出退勤・休憩打刻
- **PG04**: 勤怠一覧画面 (`/attendance/list`) - 個人の勤怠履歴表示
- **PG05**: 勤怠詳細画面 (`/attendance/detail/{id}`) - 詳細情報と修正申請
- **PG06**: 申請一覧画面 (`/stamp_correction_request/list`) - 修正申請状況確認

### 管理者機能
- **PG07**: 管理者ログイン画面 (`/admin/login`)
- **PG08**: 勤怠一覧画面 (`/admin/attendance/list`) - 全スタッフの勤怠管理
- **PG09**: 勤怠詳細画面 (`/admin/attendance/{id}`) - 個別勤怠詳細
- **PG10**: スタッフ一覧画面 (`/admin/staff/list`) - スタッフ管理
- **PG11**: スタッフ別勤怠一覧画面 (`/admin/attendance/staff/{id}`)
- **PG12**: 申請一覧画面（管理者） - 修正申請管理
- **PG13**: 修正申請承認画面 (`/admin/stamp_correction_request/approve/{id}`)

## 🔐 テストアカウント

### 管理者アカウント
- **メールアドレス**: `admin@example.com`
- **パスワード**: `password`

### 一般ユーザーアカウント
- **メールアドレス**: `yamada@example.com`
- **パスワード**: `password`
- **メールアドレス**: `tanaka@example.com`
- **パスワード**: `password`

## 環境構成

- **PHP**: 8.2-fpm
- **Laravel**: 最新版
- **MySQL**: 8.0
- **Nginx**: Alpine
- **phpMyAdmin**: 最新版
- **TailwindCSS**: 最新版 + @tailwindcss/forms プラグイン
- **Node.js**: 最新版（Vite、TailwindCSS用）

## セットアップ手順

### 1. 初回セットアップ

```bash
# セットアップスクリプトを実行可能にする
chmod +x setup.sh

# セットアップを実行
./setup.sh

# テストデータ（管理者・ユーザーアカウント）を投入
docker-compose exec app php artisan db:seed
```

### 2. アプリケーションの起動

```bash
# コンテナを起動
docker-compose up -d

# コンテナの状態を確認
docker-compose ps
```

### 3. アプリケーションの停止

```bash
# コンテナを停止
docker-compose down
```

## アクセス情報

| サービス | URL | 説明 |
|---------|-----|------|
| アプリケーション | http://localhost:8080 | Laravel アプリケーション |
| phpMyAdmin | http://localhost:8081 | データベース管理画面 |

## データベース情報

- **ホスト**: localhost (外部接続時) / db (コンテナ内)
- **ポート**: 3306
- **データベース名**: attendance_db
- **ユーザー名**: laravel_user
- **パスワード**: laravel_password

## よく使うコマンド

### Artisanコマンドの実行
```bash
docker-compose exec app php artisan [command]
```

### Composerコマンドの実行
```bash
docker-compose exec app composer [command]
```

### NPMコマンドの実行
```bash
docker-compose exec app npm [command]
```

### TailwindCSSの開発用ビルド（ホットリロード）
```bash
docker-compose exec app npm run dev
```

### TailwindCSSの本番用ビルド
```bash
docker-compose exec app npm run build
```

### ログの確認
```bash
# アプリケーションログ
docker-compose logs app

# Nginxログ
docker-compose logs nginx

# MySQLログ
docker-compose logs db
```

## TailwindCSSの使用方法

### 事前定義されたクラス

セットアップ時に以下のカスタムクラスが定義されます：

```css
/* ボタンスタイル */
.btn           /* 基本ボタンスタイル */
.btn-primary   /* プライマリボタン（青） */
.btn-secondary /* セカンダリボタン（グレー） */
.btn-success   /* 成功ボタン（緑） */
.btn-danger    /* 危険ボタン（赤） */

/* フォームスタイル */
.form-input    /* 入力フィールド */
.form-label    /* ラベル */

/* カードスタイル */
.card          /* カードコンテナ */
.card-header   /* カードヘッダー */
.card-body     /* カードボディ */
```

### 使用例

```html
<!-- ボタンの例 -->
<button class="btn btn-primary">保存</button>
<button class="btn btn-danger">削除</button>

<!-- フォームの例 -->
<label class="form-label">名前</label>
<input type="text" class="form-input" placeholder="名前を入力">

<!-- カードの例 -->
<div class="card">
    <div class="card-header">
        <h3>勤怠情報</h3>
    </div>
    <div class="card-body">
        <p>内容...</p>
    </div>
</div>
```

## ディレクトリ構成

```
attendance_app/
├── docker/                 # Docker設定ファイル
│   ├── nginx/              # Nginx設定
│   ├── php/                # PHP設定
│   └── mysql/              # MySQL設定
├── src/                    # Laravelアプリケーション
├── docker-compose.yml      # Docker Compose設定
├── setup.sh               # セットアップスクリプト
└── README.md              # このファイル
```

## トラブルシューティング

### パーミッションエラーが発生する場合
```bash
# storageとbootstrap/cacheディレクトリの権限を修正
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### データベース接続エラーが発生する場合
```bash
# データベースコンテナが起動しているか確認
docker-compose ps db

# .envファイルのデータベース設定を確認
cat src/.env | grep DB_
```

### コンテナが起動しない場合
```bash
# ログを確認
docker-compose logs

# コンテナを再構築
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```
