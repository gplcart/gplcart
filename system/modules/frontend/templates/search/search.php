<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (empty($results)) { ?>
<?php echo $this->text('No results found. Try another search keyword'); ?>
<?php } else { ?>
<?php if(!empty($navbar)) { ?>
<?php echo $navbar; ?>
<?php } ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $results; ?>
    <?php if (!empty($_pager)) { ?>
    <?php echo $_pager; ?>
    <?php } ?>
  </div>
</div>
<?php } ?>