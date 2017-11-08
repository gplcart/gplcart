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
<?php if(!empty($bookmarks)) { ?>
<div class="table-responsive">
  <table class="table bookmarks">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Path'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_created; ?>"><?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i></a></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bookmarks as $bookmark) { ?>
      <tr>
        <td>
          <a href="<?php echo $this->url($bookmark['path']); ?>">
            <?php if (empty($bookmark['title'])) { ?>
            <?php echo $this->e($bookmark['path']); ?>
            <?php } else { ?>
            <?php echo $this->e($bookmark['title']); ?>
            <?php } ?>
          </a>
        </td>
        <td><?php echo $this->date($bookmark['created']); ?></td>
        <td>
          <?php if($this->access('bookmark_delete')) { ?>
          <a href="<?php echo $this->url('admin/bookmark/delete', array('path' => $bookmark['path'], 'target' => $_path)); ?>"><?php echo $this->lower($this->text('Delete')); ?></a>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<?php if(!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php } ?>
