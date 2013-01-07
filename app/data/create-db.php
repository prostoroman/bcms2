<?php

require '../app/vendor/autoload.php';

// Connect to the demo database file
ORM::configure('sqlite:../app/data/bmcs.sqlite');

// This grabs the raw database connection from the ORM
// class and creates the table if it doesn't already exist.
// Wouldn't normally be needed if the table is already there.
$db = ORM::get_db();
$db->exec("
    CREATE TABLE IF NOT EXISTS `b_pages` (
      `id` INTEGER PRIMARY KEY,
      `name_menu` TEXT,
      `name_title` TEXT,
      `name_page` TEXT,
      `name_url` TEXT,
      `redirect_url` TEXT,
      `is_visible` BOOLEAN,
      `parent` INTEGER,
      `order` INTEGER,
      `template` TEXT,
      `content` TEXT,
      `url` TEXT,
      `date_created` TEXT,
      `date_changed` TEXT,
      `has_childs`  BOOLEAN
    );"
);
?>