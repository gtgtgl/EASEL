# EASELのリリース手順

## 仕組み

GitHubで正式Releaseを公開すると、`.github/workflows/release.yml`が動きます。
push・タグ作成だけ・下書き保存・Releaseの編集では動きません。Pre-releaseは配布対象外です。

1. Releaseのコミットを取得し、`style.css`、`theme-update.json`、Releaseタグのバージョンを確認します。
2. 配布用ZIP・更新JSON・SHA-256チェックサム・収録ファイル一覧を作成し、ActionsのArtifactとして30日間保存します。
3. 本番転送を有効にした場合だけ、`sakura-production` Environmentの保護ルールに従って転送ジョブを開始します。
4. SSH/SCPでサーバの一時ディレクトリへ両ファイルを送り、サーバ上でチェックサムを検証します。
5. 完成したZIPを`easel.zip`へ置き換えた後、JSONを`theme-update.json`へ置き換えます。

**初期状態では本番転送は無効です。** `SAKURA_DEPLOY_ENABLED`を明示的に`true`にするまで、サーバには接続しません。

公開先は既存の更新チェッカーに合わせています。

- ZIP: `https://easel.gt-gt.org/downloads/easel.zip`
- JSON: `https://easel.gt-gt.org/downloads/theme-update.json`

## 最初に手動で設定するもの

### 1. バージョンを揃える

例えば1.7を出す場合は、次の3つを揃えます。

| 場所 | 値の例 |
| --- | --- |
| `style.css`の`Version:` | `1.7` |
| `theme-update.json`の`version` | `1.7` |
| GitHub Releaseのタグ | `v1.7`または`1.7` |

`1.7`と`1.7.0`は別の値として扱います。正式な番号を決めて揃えてください。ワークフローは番号を自動変更しません。

JSONの`details_url`はその版の説明ページ、`download_url`は上記ZIPのURLにします。

### 2. さくらのSSH接続と公開先を確認する

さくらのサーバコントロールパネルでSSHを利用できる契約か確認し、公開鍵認証を設定してください。
Actions専用のSSH鍵を用意し、公開鍵をさくら側へ登録します。秘密鍵はGitや配布ZIPへ入れません。
この仕組みは、パスフレーズなしの専用秘密鍵を使う構成です。パスワード認証は使いません。

後で設定する公開先ディレクトリは、`/downloads/`に対応する**サーバ上の絶対パス**です。
例えば`/home/ACCOUNT/www/downloads`のような形ですが、独自ドメインの公開フォルダー設定により異なります。
既存の`easel.zip`と`theme-update.json`が置かれている実際の場所を確認してください。
ディレクトリは事前に作成し、SSHユーザーが書き込める状態にします。URLをそのまま入力してはいけません。

サーバ側で`sh`、SCP、`sha256`または`sha256sum`が使えることも確認してください。
FreeBSDの`sha256`とLinuxの`sha256sum`の両方に対応しています。

SSHのホスト公開鍵は、信頼できる既存の接続などで指紋を照合してから登録してください。
`ssh-keyscan`の出力を使う場合も、取得しただけでは接続先の本人確認になりません。
`known_hosts`形式の行を保存し、標準以外のポートでは`[ホスト名]:ポート番号`形式にします。
ワークフローではその登録済みの鍵と一致するサーバにだけ接続します。

参考: [さくらのSSH接続方法](https://help.sakura.ad.jp/rs/2247/)、[公開鍵認証の設定](https://help.sakura.ad.jp/rs/2804/)。

### 3. GitHubのEnvironmentと承認を設定する

リポジトリの **Settings → Environments → New environment** から、`sakura-production`を作成します。

そのEnvironmentで **Required reviewers** を有効にし、承認する人を登録してください。
自分でReleaseを公開して自分で承認する運用なら、**Prevent self-reviewは有効にしない**でください。
この設定をすると、成果物を作成した後、アップロードの直前で承認待ちになります。

Environment名をワークフローに書くだけでは承認は必須になりません。**本番転送を有効にする前に保護ルールを設定してください。**
Required reviewersの利用可否はリポジトリの公開範囲とGitHubプランによって異なります。
利用できない場合は、最初は転送無効のまま成果物を確認してください。

参考: [GitHubのEnvironmentと承認設定](https://docs.github.com/en/actions/how-tos/deploy/configure-and-manage-deployments/manage-environments)。

### 4. Environment Secretsを登録する

`sakura-production`の **Environment secrets → Add secret** に、次の名前で登録します。

| Secret名 | 入れるもの |
| --- | --- |
| `SAKURA_SSH_HOST` | SSH接続先のホスト名。例: `ACCOUNT.sakura.ne.jp` |
| `SAKURA_SSH_USER` | SSHログインのユーザー名 |
| `SAKURA_SSH_PORT` | SSHポート。通常は`22` |
| `SAKURA_SSH_PRIVATE_KEY` | 秘密鍵ファイルの全文。BEGIN/END行も含めます |
| `SAKURA_SSH_KNOWN_HOSTS` | 指紋を照合した接続先のホスト公開鍵。`known_hosts`形式 |
| `SAKURA_DEPLOY_PATH` | 既存の配布ファイルを置くサーバ上の絶対ディレクトリパス |

パスは半角英数字・`_`・`.`・`-`・`/`だけを使用し、末尾の`/`を付けません。
`..`・`.`のパス要素、空白入りのパスは受け付けません。
接続情報・秘密鍵・公開先パスはすべてSecretsから取得し、ログやArtifactには出しません。

### 5. 最初はアップロードせず成果物を確認する

GitHubの **Settings → Secrets and variables → Actions → Variables** にRepository variableを作成します。

| Variable名 | 初回の値 |
| --- | --- |
| `SAKURA_DEPLOY_ENABLED` | `false` |

未登録でも無効ですが、初回は`false`と明記しておくと確認しやすくなります。
この変数はEnvironmentではなく、**Repository variable**として登録してください。

後日、コードとバージョン番号を確認してcommit・pushした後、そのコミットに新しいタグを付けてReleaseを公開します。
ワークフローとスクリプトもそのタグのコミットに含まれている必要があります。古いコミットのReleaseでは今回の仕組みは動きません。

**Actions → Release EASEL** の実行結果を開き、SummaryとArtifactを確認してください。
Artifactの中には以下が入ります。これは確認用の外側のアーカイブなので、WordPressへは中の`easel.zip`を使います。

- `easel.zip`: `easel/`フォルダーを持つテーマZIP
- `theme-update.json`: タグのコミットと同じ更新情報
- `SHA256SUMS`: 2ファイルのSHA-256
- `release-plan.json`: バージョン、コミット、公開URL、公開順序、ZIP収録ファイル一覧

Secretsの値は表示されません。接続先と公開先パスは設定時に控えた値と、さくら側の設定を照合してください。
ダウンロードしたZIPを使い捨てのWordPressへインストールし、表示と管理画面も確認してください。

### 6. 本番転送を有効にする

確認後、Repository variableの`SAKURA_DEPLOY_ENABLED`を`true`へ変更します。
次の正式Release公開時から転送ジョブが対象になり、Required reviewersを設定していれば承認待ちになります。
Actionsの **Review deployments** から成果物・バージョン・設定済みの接続先を確認して承認します。

同じReleaseの確認用実行から転送したい場合は、`true`へ変更した後で **Re-run all jobs** を実行します。
スキップ済みの転送ジョブだけでなく、全ジョブを再実行してください。再実行時もEnvironmentの保護ルールが適用されます。
古い版の実行を再実行するとその版で上書きされるため、対象のタグを必ず確認してください。

## ZIPへ含めるもの・含めないもの

ZIPには、GitにコミットされたルートのPHP、`style.css`、`screenshot.jpg`、`theme-update.json`、画像、`library/`内の実行用PHP/JS/CSS・フォント・`.mo`翻訳を含めます。
`tests/`、`.github/`、`.git/`、SCSS、`library/dev/`、ソースマップ、Markdown、隠しファイル、未追跡ファイルは含めません。
CSS/JSは既存の生成済みファイルを使います。SCSSを変更した場合は、生成済みCSSも更新してコミットしてください。
新しい拡張子の実行用ファイルが必要になった場合は、ビルドスクリプトの収録条件も更新します。

ローカルの未コミット変更は配布に入りません。ワークフローが配布するのはReleaseが指すコミットです。

## ローカル検証

Node.jsとGitが必要です。転送テストにはBashを使います。WindowsではGit Bashで実行できます。

```sh
node tests/release-build.mjs
bash -n .github/scripts/deploy-release.sh
bash tests/release-deploy.sh
```

これらのテストは一時ディレクトリを使用します。転送テストはSSH/SCPをテスト用コマンドへ置き換えるため、サーバへ接続しません。

コミット済みの内容から配布ファイルだけを作成する場合:

```sh
node .github/scripts/build-release.mjs --tag v1.7
```

バージョンが揃っていれば`dist/`へ出力します。`dist/`はGit管理対象外です。このコマンドに転送機能はありません。

## 転送失敗時

ZIP・JSONは同じ配布ディレクトリ内に作った一時ディレクトリへ送り、両方の転送・検証が成功するまで公開ファイルを置き換えません。
ファイルごとの置き換えはrenameなので、ダウンロード中の不完全なZIPを公開しません。
ZIPとJSONの2ファイル全体を同時に切り替える処理ではありません。ZIP置き換え後にJSON置き換えが失敗した場合は、ZIPだけが新しい状態になります。
自動ロールバックはしないため、原因を確認して**同じ最新版**の全ジョブを再実行してください。
失敗した転送の`.easel-release-実行ID-試行番号`ディレクトリは、必要に応じて確認後に手動削除します。
初回の本番転送前には既存ZIPとJSONを手元へバックアップしてください。

今回の実装作業ではGitHubへのpush、Release公開、本番サーバへの接続・アップロードは行っていません。
