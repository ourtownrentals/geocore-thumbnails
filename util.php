<?php
/**
 * @package ThumbnailsAddon
 */
class addon_thumbnails_util extends addon_thumbnails_info
{
  /**
  *
  */
  public $db = true;

  /**
  *
  */
  public function getUploadPath () {
    return $this->db->GetOne("SELECT `image_upload_path` FROM " . geoTables::ad_configuration_table);
  }

  /**
  *
  */
  public function getImageData ($image_id=null)
  {
    $sql = "SELECT `image_id`, `full_filename`, `thumb_filename` FROM " . geoTables::images_urls_table;

    if ($image_id) {
      $sql .= " WHERE `image_id` = '$image_id'";
    }

    return $this->db->GetAll($sql);
  }

  /**
  *
  */
  public function getThumbnailData ($image_id=null)
  {
    $sql = "SELECT `image_id`, `size_id`, `filename`, `width`, `height` FROM " . self::THUMBNAILS_TABLE;

    if ($image_id) {
      $sql .= " WHERE `image_id` = '$image_id'";
    }

    return $this->db->GetAll($sql);
  }

  /**
  *
  */
  public function addThumbnailData ($thumbnails)
  {
    $sql = $this->db->Prepare(
      "INSERT INTO " . self::THUMBNAILS_TABLE . " (`image_id`, `size_id`, `filename`, `width`, `height`) VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($thumbnails as $thumbnail) {
      $values = array(
        $thumbnail['image_id'],
        $thumbnail['size_id'],
        $thumbnail['filename'],
        $thumbnail['width'],
        $thumbnail['height']
      );
      $this->db->Execute($sql, $values);
    }
  }


  /**
  *
  */
  public function getSize ($id)
  {
    $sizes = geoAddon::getRegistry($this->name)->sizes;
    foreach ($sizes as $size) {
      if ($size['id'] == $id) { return $size; }
    }
  }

  /**
  *
  */
  public function generateThumbnail ($image, $size)
  {
    $size_id = $size['id'];
    $max_width = (int) $size['width'];
    $max_height = (int) $size['height'];
    $quality = (int) $size['quality'];

    $upload_path = $this->getUploadPath();

    $souce = "$upload_path/" . $image['full_filename'];
    $destination = "$upload_path$size_id/" . $image['thumb_filename'];

    $thumbnail = geoImage::resize($souce, $max_width, $max_height, $alwaysResize = true);

    imagejpeg($thumbnail['image'], $destination, $quality);
    imagedestroy($thumbnail);

    return array(
      'image_id' => $image['image_id'],
      'size_id' => $size_id,
      'filename' => $image['thumb_filename'],
      'width' => $thumbnail['width'],
      'height' => $thumbnail['height']
    );
  }

  /**
  *
  */
  public function getJobs ($status=null)
  {
    $sql = "SELECT `id`, `job`, `target`, `status`, `error` FROM " . self::JOBS_TABLE;

    if ($status) {
      $sql .= " WHERE `status` = '$status'";
    }

    $sql .= " ORDER BY `id` ASC";

    return $this->db->GetAll($sql);
  }

  /**
  *
  */
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


  /**
  *
  */
  public function removeJob ($job_id)
  {
    $sql = "DELETE FROM " . self::JOBS_TABLE . " WHERE `id` = '$job_id'";
    $this->db->Execute($sql);
    return true;
  }

  /**
  *
  */
  public function setJobStatus ($job_id, $status, $error=null)
  {
    $sql  = "UPDATE " . self::JOBS_TABLE;
    $sql .= " SET `status` = '$status'";

    if ($error) {
      $sql .= ", `error` = '$error'";
    }

    $sql .= " WHERE `id` = '$job_id'";
    $this->db->Execute($sql);

    return true;
  }

  /**
  *
  */
  public function runJob ($job) {
    $target = $job['target'];

    $this->setJobStatus($job['id'], 'running');

    switch ($job['job']) {
      case 'add_thumbnails':
        $error = $this->addThumbnails($target);
        break;
      case 'remove_thumbnails':
        $error = $this->removeThumbnails($target);
        break;
      case 'add_size':
        $error = $this->addSize($target);
        break;
      case 'remove_size':
        $error = $this->removeSize($target);
        break;
    }

    if ($error) {
      $this->setJobStatus($job['id'], 'failed', $error);
    } else {
      $this->setJobStatus($job['id'], 'success');
    }
  }

  public function addThumbnails ($image_id)
  {
    $error = null;

    $sizes = geoAddon::getRegistry($this->name)->sizes;
    $image = $this->getImageData($image_id);

    $thumbnails = array();
    foreach ($sizes as $size) {
      $thumbnail = $this->generateThumbnail($image[0], $size);
      array_push($thumbnails, $thumbnail);
    }

    $this->addThumbnailData($thumbnails);

    return $error;
  }

  public function removeThumbnails ($image_id)
  {
    $error = null;

    $thumbnail_data = $this->getThumbnailData($image_id);

    foreach ($thumbnail_data as $thumbnail) {
      $size_id = $thumbnail['size_id'];
      $filename = $thumbnail['filename'];

      if ($size_id && $filename) {
        $path = $this->getUploadPath() . $size_id . '/' . $filename;
        geoFile::getInstance()->unlink($path);
      }
    }

    $sql = "DELETE FROM " . self::THUMBNAILS_TABLE . " WHERE `image_id` = '$image_id'";
    $this->db->Execute($sql);

    return $error;
  }

  public function addSize ($size_id)
  {
    $error = null;

    if (!empty($size_id)) {
      $path = $this->getUploadPath() . $size_id;
      geoFile::getInstance()->mkdir($path);
    }

    $size = $this->getSize($size_id);
    $images = $this->getImageData();

    $thumbnails = array();
    foreach ($images as $image) {
      $thumbnail = $this->generateThumbnail($image, $size);
      array_push($thumbnails, $thumbnail);
    }

    $this->addThumbnailData($thumbnails);

    return $error;
  }

  public function removeSize ($size_id)
  {
    $error = null;

    if (!empty($size_id)) {
      $path = $this->getUploadPath() . $size_id;
      geoFile::getInstance()->unlink($path);
    }

    $sql = "DELETE FROM " . self::THUMBNAILS_TABLE . " WHERE `$size_id` = '$size_id'";
    $this->db->Execute($sql);

    return $error;
  }

  public function core_notify_image_insert ($vars) {
    $db = true;
    include(GEO_BASE_DIR . 'get_common_vars.php');
    $this->db = $db;

    $job = array(
      'job' => 'add_thumbnails',
      'target' => $vars['id']
    );
    $this->queueJobs(array($job));
  }

  public function core_notify_image_remove ($imageId) {
    $db = true;
    include(GEO_BASE_DIR . 'get_common_vars.php');
    $this->db = $db;

    $job = array(
      'job' => 'remove_thumbnails',
      'target' => $imageId
    );
    $this->queueJobs(array($job));
  }
}
