{$admin_messages}
<form action="" method="post">
  <fieldset>
    <legend>Thumbnail Sizes</legend>
    <div>
    <p class="page_note"></p>
      <table>
        <thead>
          <tr class="col_hdr_top">
            <th style="color:red;">X</th>
            <th>Size ID</th>
            <th>Max Width</th>
            <th>Max Height</th>
          </tr>
        </thead>
        <tbody style="text-align: center;">
        {foreach $sizes as $size}
          <tr class="{cycle values='row_color1,row_color2'}">
            <td class="{$row_color}"><input type="checkbox" name="sizes[{$size.id}][remove]" value="1" title="remove" /></td>
            <td class="{$row_color}">{$size.id}</td>
            <td class="{$row_color}">{$size.width}</td>
            <td class="{$row_color}">{$size.height}</td>
            <input type="hidden" name="sizes[{$size.id}][id]" value="{$size.id}" />
            <input type="hidden" name="sizes[{$size.id}][width]" value="{$size.width}" />
            <input type="hidden" name="sizes[{$size.id}][height]" value="{$size.height}" />
          </tr>
        {/foreach}
          <tr class="{cycle values='row_color1,row_color2'}">
            <th class="{$row_color}">New</th>
            <td class="{$row_color}">{$next_size_id}</td>
            <td class="{$row_color}"><input type="text" size="5" maxlength="5" name="sizes[new][width]" /></td>
            <td class="{$row_color}"><input type="text" size="5" maxlength="5" name="sizes[new][height]" /></td>
          </tr>
        </tbody>
      </table>
      <div class="center">
        <input type="submit" name="auto_save" class="mini_button" value="Add new size or remove marked sizes" />
      </div>
    </div>
  </fieldset>
</form>

<form action="" method="post">
  <fieldset>
    <legend>Thumbnail Tags</legend>
    <div>
    <p class="page_note"></p>
      <table>
        <thead>
          <tr class="col_hdr_top">
            <th style="color:red;">X</th>
            <th>Tag Name</th>
            <th>Size ID</th>
          </tr>
        </thead>
        <tbody>
        {foreach $tags as $tag}
          <tr class="{cycle values='row_color1,row_color2'}">
            <td class="{$row_color}"><input type="checkbox" name="tags[{$tag.id}][remove]" value="1" title="remove" /></td>
            <td class="{$row_color}"><input type="text" size="25" name="tags[{$tag.id}][name]" value="{$tag.name}" /></td>
            <td class="{$row_color}"><input type="text" size="25" name="tags[{$tag.id}][size]" value="{$tag.size}" /></td>
          </tr>
        {/foreach}
          <tr class="{cycle values='row_color1,row_color2'}">
            <th class="{$row_color}">New</th>
            <td class="{$row_color}"><input type="text" size="5" maxlength="5" name="tags[new][name]" /></td>
            <td class="{$row_color}"><input type="text" size="5" maxlength="5" name="tags[new][size]" /></td>
          </tr>
        </tbody>
      </table>
      <div class="center">
        <input type="submit" name="auto_save" class="mini_button" value="Save changes to tags" />
      </div>
    </div>
  </fieldset>
</form>
