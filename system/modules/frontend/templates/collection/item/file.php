<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if(empty($file['url'])) { ?>
<img alt="<?php echo $this->e($file['title']); ?>" src="<?php echo $this->e($file['thumb']); ?>">
<?php } else { ?>
<a href="<?php echo $this->e($file['url']); ?>"><img alt="<?php echo $this->e($file['title']); ?>" src="<?php echo $this->e($file['thumb']); ?>"></a>
<?php } ?>