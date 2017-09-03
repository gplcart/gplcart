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
<?php if(!empty($item['thumb'])) { ?>
<?php if (empty($item['collection_item']['data']['url'])) { ?>
<img class="fill" alt="<?php echo $this->e($item['title']); ?>" src="<?php echo $this->e($item['thumb']); ?>">
<?php } else { ?>
<a href="<?php echo $this->e($item['collection_item']['data']['url']); ?>"><img alt="<?php echo $this->e($item['title']); ?>" src="<?php echo $this->e($item['thumb']); ?>"></a>
<?php } ?>
<?php } ?>
