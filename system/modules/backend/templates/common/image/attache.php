<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($images)) { ?>
<?php foreach ($images as $index => $image) { ?>
<?php $file_id = empty($image['file_id']) ? '' : $image['file_id']; ?>
<div class="sortable-thumb">
  <div class="col-md-4">
    <div class="panel panel-default thumb">
      <input type="hidden" name="<?php echo $name_prefix; ?>[images][<?php echo $index; ?>][file_id]" value="<?php echo $file_id; ?>">
      <input type="hidden" name="<?php echo $name_prefix; ?>[images][<?php echo $index; ?>][path]" value="<?php echo $this->escape($image['path']); ?>">
      <input type="hidden" name="<?php echo $name_prefix; ?>[images][<?php echo $index; ?>][weight]" value="<?php echo $image['weight']; ?>">
      <div class="panel-heading clearfix">
        <span class="handle pull-left"><i class="fa fa-arrows-alt"></i></span>
        <span class="pull-right">
          <?php echo $this->text('Delete'); ?> <input type="checkbox" name="delete_images[]" value="<?php echo $file_id; ?>">
        </span>
      </div>
      <div class="panel-body">
        <div class="form-group">
          <div class="col-md-4">
            <img src="<?php echo $this->escape($image['thumb']); ?>" class="img-responsive">
          </div>
          <div class="col-md-8 small image-info">
            <?php echo $this->escape($image['path']); ?>
          </div>
        </div>
        <?php $languages = $this->prop('languages'); ?>
        <?php if (!empty($languages)) { ?>
        <ul class="nav nav-tabs">
          <li class="active">
            <a href="#image-translation-<?php echo $index; ?>-default" data-toggle="tab">
              <?php echo $this->text('Default'); ?>
            </a>
          </li>
          <?php foreach ($languages as $code => $info) { ?>
          <li>
            <a href="#image-translation-<?php echo $index; ?>-<?php echo $code; ?>" data-toggle="tab">
              <?php echo $code; ?>
            </a>
          </li>
          <?php } ?>
        </ul>
        <?php } ?>
        <div class="tab-content">
          <div class="tab-pane active" id="image-translation-<?php echo $index; ?>-default">
            <div class="form-group">
              <div class="col-md-12">
                <input class="form-control input-sm" name="<?php echo $name_prefix; ?>[images][<?php echo $index; ?>][title]" value="<?php echo isset($image['title']) ? $this->escape($image['title']) : ''; ?>" placeholder="<?php echo $this->text('Title'); ?>">
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <textarea class="form-control input-sm" name="<?php echo $name_prefix; ?>[images][<?php echo $index; ?>][description]" placeholder="<?php echo $this->text('Description'); ?>"><?php echo isset($image['description']) ? $this->escape($image['description']) : ''; ?></textarea>
              </div>
            </div>
          </div>
          <?php if (!empty($languages)) { ?>
          <?php foreach ($languages as $code => $info) { ?>
          <div class="tab-pane" id="image-translation-<?php echo $index; ?>-<?php echo $code; ?>">
            <div class="form-group">
              <div class="col-md-12">
                <input name="<?php echo $name_prefix; ?>[images][<?php echo $index; ?>][translation][<?php echo $code; ?>][title]" maxlength="255" class="form-control input-sm" value="<?php echo isset($image['translation'][$code]['title']) ? $this->escape($image['translation'][$code]['title']) : ''; ?>" placeholder="<?php echo $this->text('Title'); ?>">
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <textarea class="form-control input-sm" name="<?php echo $name_prefix; ?>[images][<?php echo $index; ?>][translation][<?php echo $code; ?>][description]" placeholder="<?php echo $this->text('Description'); ?>"><?php echo isset($image['translation'][$code]['description']) ? $this->filter($image['translation'][$code]['description']) : ''; ?></textarea>
              </div>
            </div>
          </div>
          <?php } ?>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php } ?>
<?php } ?>