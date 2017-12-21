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
<?php if (!empty($message)) { ?>
<div class="complete-message">
<?php echo $this->filter($message); ?>
</div>
<?php } ?>
<?php if (!empty($rendered)) { ?>
<?php foreach ($rendered as $string) { ?>
<?php echo $string; ?>
<?php } ?>
<?php } ?>