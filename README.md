[重要] 作業前に
=======
Git の大文字・小文字の検知を有効にしておく

```cmd
git config core.ignorecase false
```

**実行環境では、メールが誤って飛ばないように注意のこと**


バージョン
=========
[旧環境]
- CentOS release 6.8 (Final)
- mysql  Ver 14.14 Distrib 5.6.31, for Linux (x86_64) using  EditLine wrapper
- PHP 5.5.38 (cli) (built: Jul 20 2016 16:01:37) 
- nginx version: nginx/1.10.1
  built by gcc 4.4.7 20120313 (Red Hat 4.4.7-16) (GCC) 

[新環境（予定）]
- CentOS release 7.7
- mysql  5.7
- PHP 7.2
- nginx version: 最新版
  
サーバー構成
--------------------
- tasa_web WEBサーバー nginx
- tasa_app2 php 7.2 php-fpm
- tasa_db2 MySQL ※ tasa 用データベース MySQL 5.7

WEB サービス
--------------------
- http://tasa.local 
  * cus.tokyoautosalon.jp のエミュレート
  * ドキュメントルート：/var/www/virtual/cus20xx.tokyoautosalon.jp/public

hosts の準備
--------------------
tasa.local  
を 127.0.0.1 に割り当てる

docker-compose の up
--------------------

_Docker ディレクトリに移動
```
docker-compose up -d
（削除、初期化するときは docker-compose down）
```

ログイン
========================================
http://tasa.local/member/login  
ID tasadmin  
PW tasa2017


通常時の参考コマンド
========================================

開始、終了
--------------------
Docker ディレクトリに移動
```
docker-compose start
docker-compose stop
```

コンソールログイン
--------------------
_Docker ディレクトリに移動
```
docker-compose exec tasa_web bash
docker-compose exec tasa_app2 bash
docker-compose exec tasa_db2 bash
```

本番構成
==========
[旧環境]
153.120.63.48 taws0001 proxyサーバー   
1CPU メモリ 2GB 50GB

133.242.210.196　tadb0001 アプリケーションとDB   
133.242.210.24　tadb0002 アプリケーションとDB   
1CPU メモリ 4GB 100GB

末尾 196 がいつもファイルのアップロードをしたり、DBを更新しているものですね。
末尾 24 は、ファイルのシンクとレプリケーションがかかっているのか、１号機と同期されているようです。

[新環境（未定）]
