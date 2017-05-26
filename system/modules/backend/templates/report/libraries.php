<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-heading text-right">
    <a class="btn btn-default" href="<?php echo $this->url('', array('refresh' => 1)); ?>" onclick="return confirm(GplCart.text('Are you sure?'));" class="refresh">
      <?php echo $this->text('Clear cache'); ?>
    </a>
  </div>
  <div class="panel-body table-responsive">
    <table class="table table-condensed">
      <thead>
        <tr>
          <th><?php echo $this->text('ID'); ?></th>
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Type'); ?></th>
          <th><?php echo $this->text('Version'); ?></th>
          <th><?php echo $this->text('Dependencies'); ?></th>
          <th><?php echo $this->text('Status'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($libraries as $library_id => $library) { ?>
        <tr class="library<?php echo empty($library['errors']) ? '' : ' bg-danger'; ?>">
          <td><?php echo $this->e($library_id); ?></td>
          <td>
            <a data-toggle="collapse" href="#details-<?php echo $this->e($library_id); ?>">
              <?php echo $this->text($library['name']); ?>
            </a>
          </td>
          <td><?php echo $this->e($library['type']); ?></td>
          <td>
            <?php echo $this->e($library['version']); ?>
          </td>
          <td>
            <?php if(empty($library['requires']) && empty($library['required_by'])) { ?>
            <?php echo $this->text('No'); ?>
            <?php } else { ?>
            <a data-toggle="collapse" href="#details-<?php echo $this->e($library_id); ?>">
              <?php echo $this->text('Yes'); ?>
            </a>
            <?php } ?>
          </td>
            <td>
            <?php if(empty($library['errors'])) { ?>
            <?php echo $this->text('OK'); ?>
            <?php } else { ?>
            <a data-toggle="collapse" href="#details-<?php echo $this->e($library_id); ?>">
              <?php echo $this->text('Error'); ?>
            </a>
            <?php } ?>
          </td>
          <td>
            <ul class="list-inline">
              <?php if (!empty($library['url'])) { ?>
              <li><a target="_blank" href="<?php echo $this->e($library['url']); ?>">
                <?php echo $this->text('URL'); ?></a>
              </li>
              <?php } ?>
              <?php if (!empty($library['download'])) { ?>
              <li><a onclick="return confirm(GplCart.text('You are about to download the library from an external site. Continue?'));" href="<?php echo $this->e($library['download']); ?>">
              <?php echo $this->text('Download'); ?></a>
              </li>
              <?php } ?>
            </ul>
          </td>
        </tr>
        <tr class="collapse active" id="details-<?php echo $this->e($library_id); ?>">
          <td colspan="7">
            <?php if (!empty($library['description'])) { ?>
            <div class="description">
              <b><?php echo $this->text('Description'); ?>:</b>
              <p>
                <?php echo $this->text($library['description']); ?>
              </p>
            </div>
            <?php } ?>
            <?php if(!empty($library['vendor'])) { ?>
            <div class="vendor">
              <b><?php echo $this->text('Vendor'); ?>:</b>
              <p>
                <?php echo $this->text($library['vendor']); ?>
              </p>
            </div>
            <?php } ?>
            <b><?php echo $this->text('Directory'); ?>:</b>
            <p><?php echo $this->e($library['basepath']); ?></p>
            <?php if (!empty($library['requires'])) { ?>
            <div class="requires">
              <b><?php echo $this->text('Requires'); ?>:</b>
              <p>
                <?php foreach ($library['requires'] as $requires_library_id => $version) { ?>
                <?php if (isset($libraries[$requires_library_id]['name'])) { ?>
                <span class="label label-default"><?php echo $this->text($libraries[$requires_library_id]['name']); ?><?php echo $this->e($version); ?></span>
                <?php } else { ?>
                <span class="label label-danger"><?php echo $this->e($requires_library_id); ?> (<?php echo $this->text('invalid'); ?>)</span>
                <?php } ?>
                <?php } ?>
              </p>
            </div>
            <?php } ?>
            <?php if (!empty($library['required_by'])) { ?>
            <div class="required-by">
              <b><?php echo $this->text('Required by'); ?>:</b>
              <p>
                <?php foreach ($library['required_by'] as $required_by_library_id => $version) { ?>
                <?php if (isset($libraries[$required_by_library_id]['name'])) { ?>
                <span class="label label-default"><?php echo $this->text($libraries[$required_by_library_id]['name']); ?></span>
                <?php } else { ?>
                <span class="label label-danger"><?php echo $this->e($required_by_library_id); ?> (<?php echo $this->text('invalid'); ?>)</span>
                <?php } ?>
                <?php } ?>
              </p>
            </div>
            <?php } ?>
            <?php if (!empty($library['files'])) { ?>
            <div class="files">
              <b><?php echo $this->text('Files'); ?>:</b>
              <ul class="list-unstyled">
                <?php foreach($library['files'] as $file) { ?>
                <li><?php echo $this->e($file); ?></li>
                <?php } ?>
              </ul>
            </div>
            <?php } ?>
            <?php if (!empty($library['errors'])) { ?>
            <div class="errors">
              <b><?php echo $this->text('Error'); ?>:</b>
              <ul class="list-unstyled">
              <?php foreach($library['errors'] as $error){ ?>
                <li><?php echo $this->text($error[0], $error[1]); ?></li>
              <?php } ?>
              </ul>
            </div>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>