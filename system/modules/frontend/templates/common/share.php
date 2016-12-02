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
<div class="btn-group btn-group-xs">
  <button type="button" class="btn btn-default"><i class="fa fa-share-alt"></i> <?php echo $this->text('Share'); ?></button>
  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span class="caret"></span>
    <span class="sr-only"><?php echo $this->text('Toggle dropdown'); ?></span>
  </button>
  <ul class="dropdown-menu">
    <li>
      <a rel="nofollow" target="_blank" href="<?php echo $this->url('http://www.facebook.com/sharer.php', array('u' => $url), true); ?>">
        <i class="fa fa-facebook-official"></i> <?php echo $this->text('Facebook'); ?>
      </a>
    </li>
    <li>
      <a rel="nofollow" target="_blank" href="<?php echo $this->url('https://plus.google.com/share', array('url' => $url), true); ?>">
        <i class="fa fa-google-plus-square"></i> <?php echo $this->text('Google+'); ?>
      </a>
    </li>
    <li>
      <a rel="nofollow" target="_blank" href="<?php echo $this->url('https://twitter.com/share', array('url' => $url, 'text' => $title), true); ?>">
        <i class="fa fa-twitter-square"></i> <?php echo $this->text('Twitter'); ?>
      </a>
    </li>
  </ul>
</div>