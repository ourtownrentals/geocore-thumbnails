<?php
/**
 * @package ThumbnailsAddon
 */
class addon_thumbnails_admin extends addon_thumbnails_info
{
  public function init_pages ($menuName)
  {
    menu_page::addonAddPage('addon_thumbnails_status','','Status', $this->name);
    menu_page::addonAddPage('addon_thumbnails_settings','','Settings', $this->name);
    menu_page::addonAddPage('addon_thumbnails_thumbnails','','Sizes and Tags', $this->name);
  }

  public function display_addon_thumbnails_status ()
  {
    $util = geoAddon::getUtil($this->name);
    $db = true;
    include(GEO_BASE_DIR . 'get_common_vars.php');
    $util->db = $db;

    $tpl_vars = array('jobs' => $util->getJobs());

    geoView::getInstance()->setBodyTpl('admin/status.tpl', $this->name)->setBodyVar($tpl_vars);
  }

  public function update_addon_thumbnails_status ()
  {
    if (isset($_POST['action'])) {
      $action = $_POST['action'];
    } else {
      return false;
    }

    $util = geoAddon::getUtil($this->name);
    $db = true;
    include(GEO_BASE_DIR . 'get_common_vars.php');
    $util->db = $db;

    switch ($action) {
      case 'prune_successful':
        $util->removeJobs('success');
        break;
      case 'prune_failed':
        $util->removeJobs('failed');
        break;
    }
  }

  public function display_addon_thumbnails_settings ()
  {
    $reg = geoAddon::getRegistry($this->name);

    // load existing settings or submitted settings on error
    if ($reg->settings_with_error) {
      $settings = $reg->settings_with_error;
    } elseif ($reg->settings) {
      $settings = $reg->settings;
    }

    // unset settings_with_error to avoid loop
    $reg->settings_with_error = null;
    $reg->save();

    $tpl_vars = array();
    $tpl_vars['admin_messages'] = geoAdmin::m();
    $tpl_vars['settings'] = $settings;

    geoView::getInstance()->setBodyTpl('admin/settings.tpl', $this->name)->setBodyVar($tpl_vars);
  }

  public function update_addon_thumbnails_settings ()
  {
   if (isset($_POST['settings'])) {
      $settings = $_POST['settings'];
    } else {
      return false;
    }

    $admin = geoAdmin::getInstance();
    $reg = geoAddon::getRegistry($this->name);

    $errors = $this->checkSettingsInput($settings);

    // if there was an error add each error to the list of admin messages
    if ($errors) {
      foreach ($errors as $error) {
        $admin->userError($error);
      }

      $admin->userError("Settings not saved.");

      $reg->settings_with_error = $settings;
      $reg->save();
      return false;
    } else {
      $reg->settings = $settings;
      $reg->save();
      return true;
    }
  }

  public function display_addon_thumbnails_thumbnails ()
  {
    $reg = geoAddon::getRegistry($this->name);

    if (!$reg->next_size_id) {
      $reg->next_size_id = 1;
      $reg->save();
    }

    // load existing settings or submitted settings on error
    if ($reg->tags_with_error) {
      $tags = $reg->tags_with_error;
    } elseif ($reg->tags) {
      $tags = $reg->tags;
    }

    // unset settings_with_error to avoid loop
    $reg->tags_with_error = null;
    $reg->save();

    $tpl_vars = array();
    $tpl_vars['admin_messages'] = geoAdmin::m();
    $tpl_vars['next_size_id'] = $reg->next_size_id;

    if ($reg->sizes) {
      $tpl_vars['sizes'] = $reg->sizes;
    }
    if ($tags) {
      $tpl_vars['tags'] = $tags;
    }

    geoView::getInstance()->setBodyTpl('admin/thumbnails.tpl', $this->name)->setBodyVar($tpl_vars);
  }

  public function update_addon_thumbnails_thumbnails ()
  {
    if (isset($_POST['sizes'])) {
      $new_sizes = $_POST['sizes'];
    } elseif (isset($_POST['tags'])) {
      $new_tags = $_POST['tags'];
    } else {
      return false;
    }

    $admin = geoAdmin::getInstance();
    $reg = geoAddon::getRegistry($this->name);

    if ($new_tags) {
      $tags = array();

      foreach ($new_tags as $key => $tag) {
        $cleaned_result = $this->cleanTagInput($key, $tag);
        $tag = $cleaned_result[0];
        $errors = $cleaned_result[1];

        // if there was an error add each error to the list of admin messages
        if ($errors) {
          $error = true;
          foreach ($errors as $error) {
            $admin->userError($error);
          }
        }

        if (!(is_null($tag) || $tag['remove'])) {
          array_push($tags, $tag);
        }
      }

      array_multisort($tags);

      // give each tag an id field
      foreach ($tags as $key => &$tag) {
        $tag['id'] = $key;
      }

      if ($error) {
        $admin->userError("Tag settings not saved.");
        $reg->tags_with_error = $tags;
        $reg->save();
        return false;
      } else {
        $reg->tags = $tags;
        $reg->save();
        return true;
      }
    } elseif ($new_sizes) {

      $util = geoAddon::getUtil($this->name);
      $db = true;
      include(GEO_BASE_DIR . 'get_common_vars.php');
      $util->db = $db;

      $sizes = array();
      $jobs = array();

      foreach ($new_sizes as $key => $size) {
        if ($key == 'new' && $size['width'] && $size['height']) {
          $size['id'] = $reg->next_size_id;
          $reg->next_size_id = $size['id'] + 1;

          array_push($jobs, array('job' => 'add_size', 'target' => $size['id']));

          array_push($sizes, $size);
        } elseif ($size['remove'] == 1) {
          array_push($jobs, array('job' => 'remove_size', 'target' => $size['id']));
        } elseif ($key != 'new') {
          array_push($sizes, $size);
        }
      }

      $util->queueJobs($jobs);
      $reg->sizes = $sizes;
      $reg->save();
    }
    return true;
  }

  private function checkSettingsInput ($input)
  {
    $errors = array();
    return $errors;
  }

  private function checkSizeInput ($input)
  {
    $errors = array();

    // fail if empty field
    if (!$input['width'] || !$input['height']) {
      array_push($errors, 'Empty field.');
    }

    return $errors;
  }

  private function cleanTagInput ($id, $input)
  {
    $errors = array();

    // ignore a new group if field is empty
    if ($id == 'new' && (!$input['name'] && !$input['size'])) {
      return array(null, $errors);
    }

    // fail if empty field
    if (!$input['name'] || !$input['size']) {
      array_push($errors, 'Empty field.');
    }

    return array($input, $errors);
  }
}
