<form method="post" id="country-format" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <a href="<?php echo $this->url('admin/settings/country'); ?>" class="btn btn-default cancel">
          <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('country_format_edit')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <table class="table margin-top-20 country-format">
        <thead>
          <tr>
            <th>
              <?php echo $this->text('Name'); ?>
            </th>
            <th>
              <span class="hint" title="<?php echo $this->text('Only enabled items will be shown to users'); ?>">
                <?php echo $this->text('Enabled'); ?>
              </span>
            </th>
            <th>
              <span class="hint" title="<?php echo $this->text('Force users to fill in form element that represents the format item'); ?>">
                <?php echo $this->text('Required'); ?>
              </span>
            </th>
            <th>
              <span class="hint" title="<?php echo $this->text('Items are displayed to users in ascending order by weight'); ?>">
                <?php echo $this->text('Weight'); ?>
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($format as $name => $item) { ?>
          <?php if(isset($item['name'])) { ?>
          <tr>
            <td><?php echo $this->escape($item['name']); ?></td>
            <td class="middle">
              <input type="checkbox" name="format[<?php echo $name; ?>][status]" value="1"<?php echo!empty($item['status']) ? ' checked' : ''; ?>>
              <input type="hidden" name="format[<?php echo $name; ?>][weight]" value="<?php echo $item['weight']; ?>">
            </td>
            <td class="middle">
              <input type="checkbox" name="format[<?php echo $name; ?>][required]" value="1"<?php echo!empty($item['required']) ? ' checked' : ''; ?>>
            </td>
            <td class="middle">
              <i class="fa fa-arrows handle"></i> <?php echo $this->escape($item['weight']); ?>
            </td>
          </tr>
          <?php } ?>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</form>