<?php
/**
 * @package ThumbnailsAddon
 */
class addon_thumbnails_util extends addon_thumbnails_info
{
  public $db = true;

  public function queueJobs ($jobs)
  {
    $sql = $this->db->Prepare("INSERT INTO " . self::JOBS_TABLE . " (`job`, `target`) VALUES (?, ?)");

    foreach ($jobs as $job ) {
      $this->db->Execute($sql, array($job['job'], $job['target']));
    }

    return true;
  }
}
