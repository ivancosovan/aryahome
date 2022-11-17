CREATE TABLE IF NOT EXISTS `b_ctweb_yandexdelivery_region`
(
  `ID`          int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ACTIVE`      char(1)                   DEFAULT 'N',
  `NAME`        varchar(255)     NOT NULL,
  `DESCRIPTION` TEXT,
  `COLOR`       char(7)          NOT NULL DEFAULT '#000',
  `POINTS`      TEXT,
  `PRICE_FIXED` FLOAT            NOT NULL DEFAULT 0.0,
  `PRICE`       FLOAT            NOT NULL DEFAULT 0.0,
  `PRICE_FREE`  FLOAT                     DEFAULT 0,
  `PRICE_MIN`   FLOAT                     DEFAULT 0,
  PRIMARY KEY (`ID`)
);

CREATE TABLE IF NOT EXISTS `b_ctweb_yandexdelivery_store`
(
  `ID`          int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ACTIVE`      char(1)                   DEFAULT 'N',
  `NAME`        varchar(255)     NOT NULL,
  `DESCRIPTION` TEXT,
  `ADDRESS`     varchar(255),
  `POINT`       varchar(100)     NOT NULL DEFAULT '[]',
  PRIMARY KEY (`ID`)
);

CREATE TABLE IF NOT EXISTS `b_ctweb_yandexdelivery_rs_link`
(
  `REGION_ID` int(10) unsigned NOT NULL,
  `STORE_ID`  int(10) unsigned NOT NULL,
  `TYPE`      char(1),
  constraint table_name_pk
      primary key (REGION_ID,STORE_ID)
);