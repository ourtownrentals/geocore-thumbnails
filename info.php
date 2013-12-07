<?php
 /**
 * Addon information class.
 *
 * @package ThumbnailsAddon
 */
class addon_thumbnails_info
{
  public $name = 'thumbnails';
  public $version = '0.0.1';
  public $core_version_minimum = '7.3.1';
  public $title = 'Thumbnails';
  public $author = 'Evan Boyd Sosenko';
  public $description = '';
  public $auth_tag = 'ebs_addons';
  public $author_url = 'http://evansosenko.com';
  public $info_url = 'https://github.com/razor-x/geocore-thumbnails';

  public $tags = array(
    'lead_image',
    'listing_images'
  );

  public $core_events = array (
    'notify_image_insert',
    'notify_image_remove',
    'Browse_ads_display_browse_result_addRow',
    'Browse_module_display_browse_result_addRow'
  );

  const IMAGES_TABLE = '`geodesic_addon_thumbnails_images`';
  const JOBS_TABLE = '`geodesic_addon_thumbnails_jobs`';
  const RUN_JOBS_CRON = 'thumbnails:run_jobs';
}
