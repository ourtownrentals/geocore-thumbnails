{$admin_messages}
<table style="width: 100%;">
  <thead>
    <tr class="col_hdr_top">
      <th>Job ID</th>
      <th>Status</th>
      <th>Job Action</th>
      <th>Job Target Size or Image</th>
    </tr>
  </thead>
  <tbody>
  {foreach $jobs as $job}
    <tr class="{cycle values='row_color1,row_color2'}">
      <td class="{$row_color}">{$job.id}</td>
      <td class="{$row_color}">
        {$job.status}
      {if $job.error}
        <img style="border-style: none;" src='admin_images/help.gif' alt='' class="tooltip" />
        <span class="tooltipTitleSpan" style="display: none;">Error!</span><span class="tooltipTextSpan" style="display: none;">{$job.error}</span>
      {/if}
      </td>
      <td class="{$row_color}">{$job.job}</td>
      <td class="{$row_color}">{$job.target}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
