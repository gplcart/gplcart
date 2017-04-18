<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($items)) { ?>
<div class="row section collection collection-file">
  <div class="col-md-12">
    <ul class="list-unstyled">
      <?php foreach ($items as $item) { ?>
      <li><?php echo $item['rendered']; ?></li>
      <?php } ?>
    </ul>
  </div>
</div>
<?php } ?>