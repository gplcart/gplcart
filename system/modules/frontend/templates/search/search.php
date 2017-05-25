<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if(!empty($navbar)) { ?>
<?php echo $navbar; ?>
<?php } ?>
<?php if (!empty($results)) { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $results; ?>
    <?php if (!empty($_pager)) { ?>
    <?php echo $_pager; ?>
    <?php } ?>
  </div>
</div>
<?php } ?>