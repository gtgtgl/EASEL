# EASEL 互換性と検証

## Gutenbergのリンクボタン

ブロックの挿入一覧から「EASEL リンクボタン（青／赤／黄／緑／ネイビー／灰色）」を選べます。文字はボタン内で直接編集し、右側の「リンク設定」でURLと新しいタブの設定を変更できます。

「リンク設定」の「ボタンの配置」で左揃え・中央揃え・右揃えを選択できます。編集画面と公開ページで、行の幅を確保してボタンを配置します。

クラシックエディタと同じ `blue-btn` などの色クラスを使用し、編集画面と公開ページで共通CSSを読み込みます。API version 3で登録し、新しいタブのリンクには `noopener noreferrer` を保存します。

検証: `node tests/link-buttons.js` とWordPress統合テストで6種類の登録・編集・保存内容・共有CSSの指定を確認します。

## EASEL 1.6.1の取り込み（2026-10-25）

- 連携先: https://github.com/gtgtgl/EASEL （`origin/master`）
- 取り込み元: `608244e`（1.6.1のGutenberg改善を含むコミット）
- テーマと更新情報JSONのバージョンを1.6.1へ更新。
- Gutenbergの作品タイプ全件取得と親子階層表示を取り込み。100件単位の取得終端と親が取得結果にない場合も処理。
- PHP 7.4 / 8.5対応、ブロックAPI v3、画像取得失敗時の処理、クラシックエディタのアイコン位置、Gutenberg用CSSなど、これまでのローカル修正を保持。
- 上流の `isset($theme->Version)` によるバージョン指定は、ローカルの `$theme->get('Version')` 相当の修正を維持。
- ローカル修正は未コミットの作業差分として残しています。

JavaScriptの回帰テスト: `node tests/block-editor.js`。101件の分類、ちょうど100件の分類、親のない分類、保存済みショートコードの形式とAPI v3のラッパーを確認します。

PHP 7.4で解釈できる構文を維持し、WordPress 7系とPHP 8.5向けにテーマを更新しています。

## 実施済みの検証（2026-10-25）

| WordPress | PHP | 結果 |
| --- | --- | --- |
| 7.1.3 | 7.4.33 | 全PHP構文チェック、更新チェッカー回帰テスト、WordPress統合テストに合格 |
| 7.1.3 | 8.5.12 | 全PHP構文チェック、更新チェッカー回帰テスト、WordPress統合テストに合格 |

一時的なWordPressとMySQL 8.0.31を使用し、既存サイトのデータベースとコアは変更していません。統合テストでは警告と非推奨通知も例外として扱います。実ブラウザーでの表示、エディター操作、他のプラグインや子テーマとの組み合わせは別途確認が必要です。

## 主な変更

- タクソノミーを `init` で登録し、作品タグをREST APIに公開。
- ブロックのPHP/JavaScript登録名を `easel/shortcode` に統一し、API version 3へ対応。既存記事の保存HTMLとショートコードの書式を維持。
- 古い `media_buttons_context` を `media_buttons` に置換。設定画面のタブを適切な管理画面フックで読み込み。
- 更新チェッカーの動的プロパティ生成と古い `get_themes()` フォールバックを削除。無効な応答・空のトランジェント・古いキャッシュを処理。
- ショートコードの `extract()` と文字列によるクエリ生成を置換し、`showtype=false` / `showtag=false` を正しく判定。
- 投稿・ターム・画像取得失敗時の処理、カレンダーフィルターの戻り値と未初期化変数を修正。
- `wp_body_open()`、言語属性、HTML5サポートを追加。不要な旧IE用メタ情報とModernizrの読み込みを削除。
- スタイル・スクリプトのバージョン指定、翻訳ファイルのパス、ウィジェットの出力と保存処理を修正。
- カスタムCSSとターム説明の保存で権限・nonce・入力値を確認。

## テストの再実行

ネットワークとWordPressが不要なテスト:

```sh
php tests/update-checker.php
```

WordPress統合テストはテスト用の投稿・ターム・オプションを作成・変更します。必ず使い捨てのWordPressにテーマを配置・有効化して実行してください。

```sh
wp --path=/path/to/disposable-wordpress theme activate easel
wp --path=/path/to/disposable-wordpress eval-file /path/to/easel/tests/wordpress.php
```

GitHub Actionsの `.github/workflows/compatibility.yml` にはPHP 7.4 / 8.5とWordPress 7.0 / 7.1.3の4組を設定しています。このワークフロー自体はローカルでは実行していません。

## 公式情報

- [WordPressリリース一覧](https://wordpress.org/download/releases/)
- [WordPressとPHPの互換性](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/)
- [PHP 8.5で非推奨になった機能](https://www.php.net/manual/en/migration85.deprecated.php)
