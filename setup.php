<?php
/**
 * @package ThumbnailsAddon
 */

require_once ADDON_DIR . 'thumbnails/info.php';

class addon_thumbnails_setup extends addon_thumbnails_info
{
  public function install ()
  {
    $db = $cron = $admin = true;
    include(GEO_BASE_DIR . 'get_common_vars.php');

    $sql[] =  "CREATE TABLE IF NOT EXISTS " . self::IMAGES_TABLE . " (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `image_id` int(10) unsigned NOT NULL,
      `size_id` int(3) unsigned NOT NULL,
      `filename` varchar(20) NOT NULL,
      `width` int(5) unsigned NOT NULL,
      `height` int(5) unsigned NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique` (`image_id`,`size_id`)
      ) DEFAULT CHARSET=utf8;";

    $sql[] =  "CREATE TABLE IF NOT EXISTS " . self::JOBS_TABLE . " (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `job` enum('add_thumbnail','remove_thumbnail','add_size','remove_size') NOT NULL,
      `target` int(4) unsigned NOT NULL,
      PRIMARY KEY (`id`)
      ) DEFAULT CHARSET=utf8;";

    $errors = $this->executeSQL($sql, $db);

    if (!empty($errors)) {
      foreach ($errors as $error) {
        $admin->userError('Database execution error, install failed: ' . $error);
      }
      return false;
    }

    array(
      'name'     => self::RUN_JOBS_CRON,
      'type'     => 'addon',
      'interval' => 10
    );

    $add_cron_result = $cron->set(self::RUN_JOBS_CRON, 'addon', 10);
    if (!$add_cron_result) {
      $admin->userError('Cron task run_jobs failed to be added.');
      return false;
    }

    return true;
  }

  public function uninstall ()
  {
    $db = $cron = $admin = true;
    include(GEO_BASE_DIR . 'get_common_vars.php');

    $sql[] = 'DROP TABLE IF EXISTS ' . self::IMAGES_TABLE;
    $sql[] = 'DROP TABLE IF EXISTS ' . self::JOBS_TABLE;

    $errors = $this->executeSQL($sql, $db);

    if (!empty($errors)) {
      foreach ($errors as $error) {
        $admin->userError('Database execution error, un-install failed: ' . $error);
      }
      return false;
    }

    $remove_cron_result = $cron->rem(self::RUN_JOBS_CRON);
    if (!$remove_cron_result) {
      $admin->userError('Error removing run_jobs cron task, un-install failed.');
      return false;
    }

    return true;
  }

  private function executeSQL ($sql, $db)
  {
    foreach($sql as $query) {
      $result = $db->Execute($query);
      if (!$result) {
        $errors[] = $db->ErrorMsg();
      }
    }

    return $errors;
  }
}
