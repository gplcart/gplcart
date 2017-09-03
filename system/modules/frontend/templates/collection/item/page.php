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
<div class="page item col-md-12">
  <div class="title">
    <a href="<?php echo empty($item['url']) ? $this->url("page/{$item['page_id']}") : $this->e($item['url']); ?>">
      <?php echo $this->e($item['title']); ?>
    </a>
  </div>
  <p><?php echo $this->e($this->truncate(strip_tags($item['description']), 50)); ?></p>
</div>