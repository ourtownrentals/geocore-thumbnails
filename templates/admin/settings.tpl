{$admin_messages}
<form action="" method="post">
  <fieldset>
    <legend>Settings</legend>
    <div>

      <div class="{cycle values='row_color1,row_color2'}">
        <div class="leftColumn">
          Missing Image
          <img style="border-style: none;" src='admin_images/help.gif' alt='' class="tooltip" />
          <span class="tooltipTitleSpan" style="display: none;">missing image</span><span class="tooltipTextSpan" style="display: none;">url to missing image</span>
        </div>
        <div class="rightColumn"><input type="text" size="70" name="settings[missing_image]" value="{$settings.missing_image}" /></div>
        <div class="clearColumn"></div>
      </div>

      <div class="center">
        <input type="submit" name="auto_save" class="mini_button" value="Save" />
      </div>
    </div>
  </fieldset>
</form>
