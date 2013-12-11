<?php
/**
 * @package ThumbnailsAddon
 */
class addon_thumbnails_util extends addon_thumbnails_info
{
  public $db = true;

  public function getJobs ($status=null)
  {
    $sql = "SELECT `id`, `job`, `target`, `status`, `error` FROM " . self::JOBS_TABLE;

    if ($status) {
      $sql .= " WHERE `status` = '$status'";
    }

    $sql .= " ORDER BY `id` ASC";

    return $this->db->GetAll($sql);
  }

  public function queueJobs ($jobs)
  {
    $sql = $this->db->Prepare("INSERT INTO " . self::JOBS_TABLE . " (`job`, `target`) VALUES (?, ?)");

    foreach ($jobs as $job ) {
      $this->db->Execute($sql, array($job['job'], $job['target']));
    }

    return true;
  }

  /**
  *
  */
  public function removeJobs ($status)
  {
    $sql = "DELETE FROM " . self::JOBS_TABLE . " WHERE `status` = '$status'";
    $this->db->Execute($sql);
    return true;
  }
}
