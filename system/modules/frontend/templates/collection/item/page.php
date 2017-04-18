<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="page item col-md-12">
  <div class="title"><a href="<?php echo $this->e($page['url']); ?>"><?php echo $this->e($page['title']); ?></a></div>
  <p><?php echo $this->e($this->truncate(strip_tags($page['description']), 50)); ?></p>
</div>