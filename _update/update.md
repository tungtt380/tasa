# 新年度に行うこと

# 事前準備
nginx のドメイン設定

su:egpegb2sf8

## ディレクトリのコピー
mkdir /var/www/virtual/cus2020.tokyoautosalon.jp 

新本番環境
```
cp -Ra /var/www/virtual/cus2019.tokyoautosalon.jp/* /var/www/virtual/cus2020.tokyoautosalon.jp
```


## データベースのバックアップ
```
mysqldump -R -h192.168.233.11 -utasa -p tasdev19 > tasdev19_with_function.20190619.sql
mysqldump -R -h192.168.233.11 -utasa -p tasa2019 > tasa2019_with_function.20190619.sql
```


# データベースの作成
su でログインしたまま、mysql でログイン（ユーザー、パスワードの指定無し）

# 本年度データベースの作成
```
CREATE DATABASE IF NOT EXISTS `tasdev20` DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`;
GRANT ALL ON `tasdev20`.* TO 'tasa'@'%';
CREATE DATABASE IF NOT EXISTS `tasa2020` DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`;
GRANT ALL ON `tasa2020`.* TO 'tasa'@'%';
```

## 昨年データのインストール
```
mysql -h192.168.233.11 -utasa -p tasdev20 < tasdev19_with_function.20190619.sql   
mysql -h192.168.233.11 -utasa -p tasa2020 < tasa2019_with_function.20190619.sql   
```

# Git の準備

- develop から、2020 ブランチをを作成する

 cus2020.tokyoautosalon.jp ディレクトリは 2020 に checkout する

- develop から、必要に応じて feature/2020-xxx を作成する

 cus.hornet-works ディレクトリ は、develop に checkout する


# PHP の修正

## application/database.php の修正

※ 昨年分は$db['2020']にコピーしておく

```
$db['default']['database'] = 'tasa2021';
・
・
・
if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp' || $_SERVER['HTTP_HOST'] == 'cus2020.tokyoautosalon.jp') {
    $db['default']['db_debug'] = FALSE;
} else {
    $db['default']['database'] = 'tasdev20';
}
```


# データの初期化

## 更新データの初期化
```
truncate table exhibitors;
truncate table exhibitor_application;
truncate table exhibitor_apply;
truncate table exhibitor_bill;
truncate table exhibitor_booth;
truncate table exhibitor_contact;
truncate table exhibitor_dist;
truncate table exhibitor_manager;
truncate table histories;
truncate table invoice;
truncate table invoice_detail;
truncate table member_autologin;
truncate table payment;
truncate table payment_detail;
```

## シークエンス
```
update sequence set id = 20199 where name = 'events.eventid';
update sequence set id = 200000 where name = 'exhibitor_bill.billid';
update sequence set id = 200000 where name = 'exhibitors.exhid';
update sequence set id = 2020000 where name = 'members.memberid';
```

## 昨年の顧客マッチングの引継ぎ情報（customerとexhibitorの紐つけ情報）を削除する
```
update customers set exhid = null where 1;
```

## 昨年のメンバーを削除する
```
select * from members where memberid >= 'M0002019000' and memberid < 'M0002020000';
```

## 2020 年のイベントを作成する

** 管理画面から 2020 年の新規イベントを作成 **

## view の更新
### v_spaces
```
drop view v_spaces;
CREATE VIEW `v_spaces`  AS  select `spaces`.`spaceid` AS `spaceid`,
`spaces`.`eventid` AS `eventid`,
`spaces`.`seqno` AS `seqno`,
`spaces`.`spacename` AS `spacename`,
`spaces`.`spaceabbr` AS `spaceabbr`,
`spaces`.`memberprice` AS `memberprice`,
`spaces`.`assocprice` AS `assocprice`,
`spaces`.`maxspaces` AS `maxspaces`,
`spaces`.`comments` AS `comments`,
`spaces`.`carlimits` AS `carlimits`,
`spaces`.`total_count` AS `total_count`,
`spaces`.`notsale_count` AS `notsale_count`,
`spaces`.`forsale_count` AS `forsale_count`,
`spaces`.`inventory` AS `inventory`,
`spaces`.`token` AS `token`,
`spaces`.`expired` AS `expired`,
`spaces`.`created` AS `created`,
`spaces`.`updated` AS `updated`,
`spaces`.`deleted` AS `deleted` from `spaces` where ((`spaces`.`eventid` = 'AS0201*0') and (`spaces`.`expired` = 0)) ;
```

## spaces に昨年の続きデータを挿入

新しい区分けができるかもしれないが、一旦は昨年のデータのスペースと同じものを入れる
※ 昨年の最終データから持ってくること
```
INSERT INTO `spaces` VALUES
(2001,'AS020200',1010,'Aスペース','A',270000,324000,50,'（基本設備付）他スペース併用不可',0,0,0,50,1,'1',0,'2011-06-13 01:27:31','2016-07-27 01:11:12',NULL)・・・
```

## booth のデータを入れ替え

こちらも一旦は昨年データを踏襲したものを年度だけ入れ替えて
※ 昨年の最終データから持ってくること
```
delete from booths where 1;

INSERT INTO `booths`
(`boothid`,`eventid`,`boothno`,`spaceid`,`boothname`,`boothabbr`,`boothcount`,`allow`,`token`,`expired`,`created`,`updated`,`deleted`)
 VALUES
(xxx,'AS020200',11,2001,'A1x1','1x1',1,1,'1',0,'2011-04-27 23:35:34','2016-07-01 00:28:42',NULL)
・・・
```

applications/space_models の
get_dropdown の検索値を 2019 -> 2020 に変更


# テキストの修正

全文検索で 2019 を検索し、手動で置き換える・・しかなさそう。

何気にけっこうある。

メール文面などは指示をもらう。

# ベーシック認証がついていたら外す
op/entry.php
op/ja/entry.php
op/en/entry.php

# サービスのパーミッションを 0777 に
application/config/service.php

# キャンセル待ちの確認
サービスページで、キャンセル待ちになっていたら、受付にする
