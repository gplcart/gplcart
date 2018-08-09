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
<?php if (!empty($pages)) { ?>
<?php foreach ($pages as $page_id => $page) { ?>
<div class="card post" id="post-<?php echo $this->e($page_id); ?>">
  <p><?php echo $this->date($page['created']); ?></p>
  <h3 class="h4"><?php echo $this->e($page['title']); ?></h3>
  <?php if (isset($page['teaser'])) { ?>
  <div class="description"><?php echo $this->e($page['teaser']); ?></div>
  <p>
    <?php if(empty($page['url'])) { ?>
    <a href="<?php echo $this->url("page/$page_id"); ?>"><?php echo $this->text('Read more'); ?></a>
    <?php } else { ?>
    <a href="<?php echo $this->e($page['url']); ?>"><?php echo $this->text('Read more'); ?></a>
    <?php } ?>
  </p>
  <?php } else { ?>
  <div class="description"><?php echo $this->filter($page['description']); ?></div>
  <?php } ?>
</div>
<?php } ?>
<?php if (!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
<?php } else { ?>
<?php echo $this->text('No posts yet'); ?>
<?php } ?>
