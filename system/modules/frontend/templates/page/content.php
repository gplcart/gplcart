<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="row page">
  <?php if (!empty($images)) { ?>
  <div class="col-md-3"><?php echo $images; ?></div>
  <?php } ?>
  <div class="<?php echo empty($images) ? 'col-md-12' : 'col-md-9'; ?>">
    <?php echo $this->filter($page['description']); ?>
  </div>
</div>