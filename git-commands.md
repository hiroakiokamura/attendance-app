# Git コマンド集

勤怠管理アプリ開発で使用する便利なGitコマンドをまとめました。

## 基本的なワークフロー

### 変更をコミットしてプッシュ
```bash
# 変更されたファイルを確認
git status

# すべての変更をステージング
git add .

# 特定のファイルのみステージング
git add ファイル名

# コミット
git commit -m "コミットメッセージ"

# GitHubにプッシュ
git push
```

### ブランチ操作
```bash
# 新しいブランチを作成して切り替え
git checkout -b feature/新機能名

# ブランチを切り替え
git checkout ブランチ名

# ブランチ一覧を表示
git branch

# リモートブランチも含めて表示
git branch -a

# ブランチをマージ（mainブランチに戻ってから）
git checkout main
git merge feature/新機能名

# ブランチを削除
git branch -d feature/新機能名
```

### 履歴とログ
```bash
# コミット履歴を表示
git log

# 簡潔な履歴を表示
git log --oneline

# ファイルの変更履歴を表示
git log -p ファイル名
```

## 開発フロー例

### 新機能開発の流れ
```bash
# 1. 最新の状態に更新
git pull

# 2. 新機能用ブランチを作成
git checkout -b feature/user-authentication

# 3. 開発作業...

# 4. 変更をコミット
git add .
git commit -m "feat: ユーザー認証機能を実装"

# 5. GitHubにプッシュ
git push -u origin feature/user-authentication

# 6. GitHubでプルリクエストを作成

# 7. レビュー後、mainブランチにマージ
git checkout main
git pull
git branch -d feature/user-authentication
```

### バグ修正の流れ
```bash
# 1. バグ修正用ブランチを作成
git checkout -b hotfix/fix-login-error

# 2. バグを修正...

# 3. 修正をコミット
git add .
git commit -m "fix: ログインエラーを修正"

# 4. プッシュ
git push -u origin hotfix/fix-login-error
```

## コミットメッセージの書き方

### Conventional Commits形式
```
feat: 新機能追加
fix: バグ修正
docs: ドキュメント更新
style: コードスタイル変更（機能に影響なし）
refactor: リファクタリング
test: テスト追加・修正
chore: その他の変更
```

### 例
```
feat: 勤怠打刻機能を追加
fix: 時刻計算のバグを修正
docs: README.mdにセットアップ手順を追加
style: TailwindCSSクラスを整理
refactor: 勤怠計算ロジックを分離
test: 勤怠計算のテストケースを追加
chore: Docker設定を更新
```

## トラブルシューティング

### プッシュできない場合
```bash
# リモートの変更を取得してマージ
git pull

# 競合がある場合は解決してからコミット
git add .
git commit -m "merge: 競合を解決"
git push
```

### 間違ったコミットを取り消す
```bash
# 最新のコミットを取り消し（変更は保持）
git reset --soft HEAD~1

# 最新のコミットを完全に取り消し
git reset --hard HEAD~1

# 特定のコミットを取り消し
git revert コミットハッシュ
```

### ファイルを間違ってコミットした場合
```bash
# 特定のファイルを履歴から削除
git rm --cached ファイル名
git commit -m "remove: 不要なファイルを削除"
```
