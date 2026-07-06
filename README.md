# COACHTECH お問い合わせフォーム

## 概要

お問い合わせフォームを管理するアプリケーションです。
一般ユーザーはお問い合わせを送信することができ、管理者はログイン後にお問い合わせの一覧の確認、検索、削除、タグ管理を行うことができます。

## ER図

![ER図](er.drawio.png)

- categoriesテーブル 1:多 contactsテーブル
- contactsテーブル 多:多 tagsテーブル
- usersテーブル リレーションなし

## 動作環境

- Docker
- Docker Compose

※Windowsの場合はWSL2の利用を推奨します。

## 環境構築手順

### 1.リポジトリをクローン

```
git clone https://github.com/wtpda30/contact-form-app.git
```

### 2..envファイルの準備

.env.example をコピーして .env を作成します。

`cp .env.example .env`

.env ファイル内の以下のDB接続情報を確認・設定します。.env.example のデフォルト値はSail向けではないため、以下のように変更してください。

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### 3.Composer依存パッケージのインストール

プロジェクトの初回セットアップ時は、vendor ディレクトリが存在しないため sail コマンドを使用できません。 以下のDockerコマンドを実行して、コンテナ内で composer install を実行します。

```
    docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

### 4.Laravel Sailの起動

以下のコマンドでDockerコンテナを起動します。

  `./vendor/bin/sail up -d`を実行

- エイリアスの設定（推奨）

  毎回 ./vendor/bin/sail と入力するのは手間なので、エイリアスを設定すると便利です。

  `alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'`

### 5.アプリケーションキーの生成

  `sail artisan key:generate`

### 6.データベースのマイグレーションと初期データ投入

以下のコマンドでテーブルを作成し、ダミーデータを投入します。

`sail artisan migrate:fresh --seed`

このコマンドの入力後、下記のエラーが表示されることがあります。

```
Illuminate\Database\QueryException

SQLSTATE[HY000] [1044] Access denied for user 'sail'@'%' to database 'contact-form-app' (Connection: mysql, SQL: select table_name as `name`,         (data_length + index_length) as `size`, table_comment as `comment`, engine as `engine`, table_collation as `collation` from information_schema.tables where table_schema = 'contact-form-app' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED') order by table_name)

  at vendor/laravel/framework/src/Illuminate/Database/Connection.php:829

    825▕                     $this->getName(), $query, $this->prepareBindings($bindings), $e
    826▕                 );
    827▕             }
    828▕
  ➜ 829▕             throw new QueryException(
    830▕                 $this->getName(), $query, $this->prepareBindings($bindings), $e
    831▕             );
    832▕         }
    833▕     }

  +43 vendor frames

  44  artisan:35
      Illuminate\Foundation\Console\Kernel::handle()
```

このエラーはコンテナ内にデータが残っており、エラーが生じているケースなどがあります。 その場合は、以下のコマンドを順に実行して各コンテナを再起動して下さい。

```
sail down -v
sail up -d //コマンド実行後にSQLコンテナが立ち上がるまで時間がかかります。30秒ほどお待ちください。
sail artisan migrate:fresh --seed
```

### 7.フロントエンドのビルド

```
sail npm install
sail npm install alpinejs
sail npm run dev
```

`npm run dev` は開発中は起動したままにしてください。

### 8.アプリケーションへのアクセス

ブラウザで http://localhost にアクセスします。

## 使用技術

- OS(Dockerが動作する任意のOS)
- PHP 8.2
- Laravel 10.x
- DB: MySQL 8.0
- Webサーバー: Nginx
- フロントエンド: Vite, Tailwind CSS 3.4.0
- 開発ツール: Docker, Docker Compose,Laravel Sail, phpMyAdmin
- Blade テンプレート
- Laravel Fortify(認証)
- Laravel Pint

## 機能一覧

- ユーザー認証（登録、ログイン、ログアウト）
- お問い合わせ登録・一覧取得・検索・単体取得
- お問い合わせ詳細表示・削除
- CSVエクスポート
- タグ管理（追加・更新・削除）
- 公開API（お問い合わせCRUD）

## APIエンドポイント一覧

認証不要の公開APIです。全エンドポイントは /api/v1 プレフィックス配下に定義されています。

| Method | URL                        | 概要                                         |
| :----- | :------------------------- | :------------------------------------------- |
| GET    | /api/v1/contacts           | お問い合わせ一覧（検索・ページネーション付き |
| GET    | /api/v1/contacts/{contact} | お問い合わせ詳細（カテゴリ・タグ含む）       |
| POST   | /api/v1/contacts/          | お問い合わせ新規作成                         |
| PUT    | /api/v1/contacts/{contact} | お問い合わせ更新                             |
| DELETE | /api/v1/contacts/{contact} | お問い合わせ削除                             |

## テスト実行

`sail artisan test`

カバレッジ付きで実行する場合

`sail artisan test --coverage`

## 環境開発URL

http://localhost

## 作成者

宮古澄佳
