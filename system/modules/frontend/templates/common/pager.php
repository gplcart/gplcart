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
<?php if(!empty($pager['pages'])) { ?>
<ul class="pagination-unstyled list-inline">
  <?php if (!empty($pager['prev'])) { ?>
  <li><a rel="prev" href="<?php echo $this->e($pager['prev']); ?>">&laquo;</a></li>
  <?php } ?>
  <?php if(empty($options['mini'])) { ?>
  <?php foreach ($pager['pages'] as $page) { ?>
  <?php if (empty($page['url'])) { ?>
  <li class="disabled"><span><?php echo $page['num']; ?></span></li>
  <?php } else { ?>
  <?php if(empty($page['is_current'])) { ?>
  <li>
    <a href="<?php echo $page['url']; ?>"><?php echo $page['num']; ?></a>
  </li>
  <?php } else { ?>
  <li class="active">
    <?php echo $page['num']; ?>
  </li>
  <?php } ?>
  <?php } ?>
  <?php } ?>
  <?php } ?>
  <?php if (!empty($pager['next'])) { ?>
  <li><a rel="next" href="<?php echo $this->e($pager['next']); ?>">&raquo;</a></li>
  <?php } ?>
</ul>
<?php } ?>
