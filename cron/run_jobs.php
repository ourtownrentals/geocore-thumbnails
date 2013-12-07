<?php
/**
 * Make sure that this file is being accessed from cron.php.
 */
if (!defined('GEO_CRON_RUN')) {
  die('NO ACCESS');
}

return true;
