<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($images)) { ?>
<div class="row image-container">
<?php foreach ($images as $index => $image) { ?>
<?php $file_id = empty($image['file_id']) ? '' : $image['file_id']; ?>
<?php $weight = empty($image['weight']) ? 0 : $image['weight']; ?>
<div class="sortable-thumb w-100">
  <div class="<?php echo empty($single) ? 'col-md-3' : 'col-md-12'; ?>">
    <div class="card thumb">
      <input type="hidden" name="<?php echo $entity; ?>[images][<?php echo $index; ?>][file_id]" value="<?php echo $file_id; ?>">
      <input type="hidden" name="<?php echo $entity; ?>[images][<?php echo $index; ?>][path]" value="<?php echo $this->e($image['path']); ?>">
      <input type="hidden" name="<?php echo $entity; ?>[images][<?php echo $index; ?>][weight]" value="<?php echo $weight; ?>">
      <div class="card-header clearfix">
        <?php if(empty($single) && count($images) > 1) { ?>
        <span class="handle float-left"><i class="fa fa-arrows-alt"></i></span>
        <?php } ?>
        <span class="float-right">
          <?php echo $this->text('Delete'); ?> <input type="checkbox" name="delete_images[]" value="<?php echo $file_id; ?>">
        </span>
      </div>
      <div class="card-body">
        <div class="form-group">
            <img src="<?php echo $this->e($image['thumb']); ?>" class="img-fluid">
          <div class="small image-info">
            <?php echo $this->e($image['path']); ?>
          </div>
        </div>
        <?php if (!empty($languages)) { ?>
        <ul class="nav nav-tabs">
          <li class="nav-item">
            <a class="nav-link active" href="#image-translation-<?php echo $index; ?>-default" data-toggle="tab">
              <?php echo $this->text('Default'); ?>
            </a>
          </li>
          <?php foreach ($languages as $code => $info) { ?>
          <li class="nav-item">
            <a class="nav-link" href="#image-translation-<?php echo $index; ?>-<?php echo $code; ?>" data-toggle="tab">
              <?php echo $code; ?>
            </a>
          </li>
          <?php } ?>
        </ul>
        <?php } ?>
        <div class="tab-content">
          <div class="tab-pane active" id="image-translation-<?php echo $index; ?>-default">
            <div class="form-group">
                <input class="form-control" name="<?php echo $entity; ?>[images][<?php echo $index; ?>][title]" value="<?php echo isset($image['title']) ? $this->e($image['title']) : ''; ?>" placeholder="<?php echo $this->text('Title'); ?>">
            </div>
            <div class="form-group">
              <textarea class="form-control" name="<?php echo $entity; ?>[images][<?php echo $index; ?>][description]" placeholder="<?php echo $this->text('Description'); ?>"><?php echo isset($image['description']) ? $this->e($image['description']) : ''; ?></textarea>
            </div>
          </div>
          <?php if (!empty($languages)) { ?>
          <?php foreach ($languages as $code => $info) { ?>
          <div class="tab-pane" id="image-translation-<?php echo $index; ?>-<?php echo $code; ?>">
            <div class="form-group">
                <input name="<?php echo $entity; ?>[images][<?php echo $index; ?>][translation][<?php echo $code; ?>][title]" maxlength="255" class="form-control input-sm" value="<?php echo isset($image['translation'][$code]['title']) ? $this->e($image['translation'][$code]['title']) : ''; ?>" placeholder="<?php echo $this->text('Title'); ?>">
            </div>
              <div class="form-group">
                <textarea class="form-control" name="<?php echo $entity; ?>[images][<?php echo $index; ?>][translation][<?php echo $code; ?>][description]" placeholder="<?php echo $this->text('Description'); ?>"><?php echo isset($image['translation'][$code]['description']) ? $this->filter($image['translation'][$code]['description']) : ''; ?></textarea>
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
</div>
<?php } ?>
<?php if ($this->access('file_upload')) { ?>
<div class="form-group<?php echo $this->error('images', ' has-error'); ?>">
  <div class="<?php echo empty($single) ? 'col-md-4' : 'col-md-12'; ?>">
    <input type="file" class="form-control" name="files[]"<?php echo empty($single) ? ' multiple' : ''; ?>>
    <div class="form-text">
       <?php echo $this->error('images'); ?>
      <div class="text-muted">
          <?php echo $this->text('Select one or more images'); ?>
      </div>
    </div>
  </div>
</div>
<?php } ?>