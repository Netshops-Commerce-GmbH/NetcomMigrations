CREATE TABLE IF NOT EXISTS `s_plugin_netcom_migrations`
(
  `id`          INT(11) NOT NULL auto_increment,
  `version`     VARCHAR(32) collate utf8_unicode_ci NOT NULL,
  `plugin`      VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `migration`   VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_date`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finish_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
)
engine=innodb;