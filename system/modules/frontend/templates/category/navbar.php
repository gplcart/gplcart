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
<nav class="navbar navbar-unstyled category">
  <form class="navbar-form navbar-right" onchange="$(this).submit();">
    <?php echo $this->text('Showing @num of @total', array('@num' => $quantity, '@total' => $total)); ?>
    <select name="sort" class="form-control">
      <option value="price-asc"<?php echo $sort === 'price-asc' ? ' selected' : ''; ?>>
        <?php echo $this->text('Low prices first'); ?>
      </option>
      <option value="price-desc"<?php echo $sort === 'price-desc' ? ' selected' : ''; ?>>
        <?php echo $this->text('High prices first'); ?>
      </option>
      <option value="title-asc"<?php echo $sort === 'title-asc' ? ' selected' : ''; ?>>
        <?php echo $this->text('Title A-Z'); ?>
      </option>
      <option value="title-desc"<?php echo $sort === 'title-desc' ? ' selected' : ''; ?>>
          <?php echo $this->text('Title Z-A'); ?>
      </option>
    </select>
    <div class="btn-group" data-toggle="buttons">
      <label class="btn btn-default<?php echo $view === 'list' ? ' active' : ''; ?>">
        <input type="radio" name="view" autocomplete="off" value="list"<?php echo $view === 'list' ? ' checked' : ''; ?>>
        <i class="fa fa-th-list"></i>
      </label>
      <label class="btn btn-default<?php echo $view === 'grid' ? ' active' : ''; ?>">
        <input type="radio" name="view" autocomplete="off" value="grid"<?php echo $view === 'grid' ? ' checked' : ''; ?>>
        <i class="fa fa-th-large"></i>
      </label>
    </div>
    <button class="btn btn-default hidden-js"><?php echo $this->text('Go'); ?></button>
    <?php // Add hidden fields to retain additional GET parameters from the current URL (if any)  ?>
    <?php foreach ($_query as $key => $value) { ?>
    <?php if (!in_array($key, array('sort', 'view'))) { ?>
    <input type="hidden" name="<?php echo $this->e($key); ?>" value="<?php echo $this->e($value); ?>">
    <?php } ?>
    <?php } ?>
  </form>
</nav>
