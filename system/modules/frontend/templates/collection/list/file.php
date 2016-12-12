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
<?php if (!empty($files)) { ?>
<div class="row section collection collection-file">
  <div class="col-md-12">
    <ul class="slider" data-slider="true" data-slider-settings='{
        "auto": false,
        "loop": true,
        "pager": false,
        "autoWidth": true,
        "pauseOnHover": true,
        "item": 2
        }'>
      <?php foreach ($files as $file) { ?>
      <li><?php echo $file['rendered']; ?></li>
      <?php } ?>
    </ul>
  </div>
</div>
<?php } ?>