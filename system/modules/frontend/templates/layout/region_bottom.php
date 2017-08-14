<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($_js_bottom)) { ?>
<?php foreach ($_js_bottom as $js) { ?>
<?php if (!empty($js['text'])) { ?>
<?php if (!empty($js['asset'])) { ?>
<?php if (!empty($js['condition'])) { ?>
<!--[<?php echo $this->e($js['condition']); ?>]>
<script><?php echo $js['asset']; ?></script>
<![endif]-->
<?php } else { ?>
<script><?php echo $js['asset']; ?></script>
<?php } ?>
<?php } ?>
<?php } else { ?>
<?php if (!empty($js['condition'])) { ?>
<!--[<?php echo $this->e($js['condition']); ?>]>
<script src="<?php echo $this->url($js['asset'], array('v' => $js['version']), false, true); ?>"></script>
<![endif]-->
<?php } else { ?>
<script src="<?php echo $this->url($js['asset'], array('v' => $js['version']), false, true); ?>"></script>
<?php } ?>
<?php } ?>
<?php } ?>
<?php } ?>