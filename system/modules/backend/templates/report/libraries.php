<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-body">
    <?php foreach($libraries as $library_id => $library) { ?>
    <b><?php echo $this->escape($library['name']); ?></b>
    <div>
    <?php echo $this->text('Type'); ?>: <?php echo $this->escape(mb_strtoupper($library['type'])); ?>
    </div>
    <div>
    <?php echo $this->text('Version'); ?>: <?php echo $this->escape($library['version']['number']); ?>
    </div>
    <?php if(!empty($library['description'])) { ?>
    <div><?php echo $this->escape($library['description']); ?></div>
    <?php } ?>
    <div>
    <?php if(!empty($library['url'])) { ?>
    <a href="<?php echo $this->escape($library['url']); ?>"><?php echo $this->text('URL'); ?></a>
    <?php } ?>
    <?php if(!empty($library['download'])) { ?>
    <a href="<?php echo $this->escape($library['download']); ?>"><?php echo $this->text('Download'); ?></a>
    <?php } ?>
    </div>
    <hr>
    <?php } ?>
  </div>
</div>