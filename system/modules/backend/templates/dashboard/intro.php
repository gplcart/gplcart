<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($items)) { ?>
<div class="panel panel-default">
  <div class="panel-body">
    <?php foreach ($items as $item) { ?>
    <?php echo $item['rendered']; ?>
    <?php } ?>
  </div>
  <div class="panel-footer text-center">
    <a href="<?php echo $this->url('', array('skip_intro' => 1)); ?>">
      <?php echo $this->text('Skip'); ?>
    </a>
  </div>
</div>
<?php } ?>