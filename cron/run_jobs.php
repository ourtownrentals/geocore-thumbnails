<?php
/**
 * Make sure that this file is being accessed from cron.php.
 */
if (!defined('GEO_CRON_RUN')) {
  die('NO ACCESS');
}

$db = true;
include(GEO_BASE_DIR . 'get_common_vars.php');

$info = geoAddon::getInfoClass('thumbnails');
$util = geoAddon::getUtil($info->name);
$util->db = $db;

$jobs = $util->getJobs('pending');

foreach ($jobs as $job) {
  try {
    $util->runJob($job);
  } catch (Exception $exception) {
    $util->setJobStatus($job['id'], 'failed', $exception->getMessage());
  }
}

return true;
