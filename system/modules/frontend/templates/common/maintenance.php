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
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $this->text('Site maintenance'); ?></title>
  </head>
  <body>
    <h1><?php echo $this->text('We\'ll be back soon!'); ?></h1>
    <p><?php echo $this->text('Sorry for the inconvenience but we\'re performing some maintenance at the moment.'); ?></p>
  </body>
</html>