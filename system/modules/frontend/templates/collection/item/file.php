<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<?php if (empty($file['url'])) { ?>
<img alt="<?php echo $this->escape($file['title']); ?>" src="<?php echo $file['thumb']; ?>">
<?php } else { ?>
<a href="<?php echo $this->escape($file['url']); ?>"><img alt="<?php echo $this->escape($file['title']); ?>" src="<?php echo $file['thumb']; ?>"></a>
<?php } ?>
