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
<?php if (!empty($images)) { ?>
    <div class="row section">
      <div class="col-md-2"><?php echo $images; ?></div>
      <?php if (!empty($category['description_1'])) { ?>
      <div class="col-md-10"><?php echo $this->xss($category['description_1']); ?></div>
      <?php } ?>
    </div>
<?php } ?>
<?php if (!empty($children)) { ?>
    <?php echo $children; ?>
<?php } ?>
    <?php if (empty($products)) { ?>

<?php if (empty($children)) { ?>
    <div class="row">
      <div class="col-md-12">
        <?php echo $this->text('This category has no content'); ?>
      </div>
    </div>
<?php } ?>
    <?php } else { ?>
    <?php echo $navbar; ?>
    <?php echo $products; ?>
    <?php if (!empty($pager)) { ?>
    <div class="row">
      <div class="col-md-12 text-right">
        <?php echo $pager; ?>
      </div>
    </div>
    <?php } ?>
    <?php } ?>
    <?php if (!empty($category['description_2'])) { ?>
    <div class="row section description-2">
      <div class="col-md-12">
        <?php echo $category['description_2']; ?>
      </div>
    </div>
    <?php } ?>