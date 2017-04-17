<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<nav class="navbar category navbar-default">
  <div class="container-fluid">
    <span class="navbar-text navbar-left">
      <?php echo $this->text('Showing @num from @total', array('@num' => $quantity, '@total' => $total)); ?>
    </span>
    <form class="navbar-form navbar-right" onchange="$(this).submit();">
      <span class="form-control-static"><?php echo $this->text('Sort'); ?></span>
      <select name="sort" class="form-control input-sm">
        <option value="price-asc"<?php echo $sort === 'price-asc' ? ' selected' : ''; ?>>
          <?php echo $this->text('Low prices first'); ?>
        </option>
        <option value="price-desc"<?php echo $sort === 'price-desc' ? ' selected' : ''; ?>>
          <?php echo $this->text('High prices first'); ?>
        </option>
        <option value="title-asc"<?php echo $sort === 'title-asc' ? ' selected' : ''; ?>>
          <?php echo $this->text('Title A-Z'); ?>
        </option>
        <option value="title-desc"<?php echo $sort === 'price-desc' ? ' selected' : ''; ?>>
          <?php echo $this->text('Title Z-A'); ?>
        </option>
      </select>
      <div class="btn-group btn-group-sm" data-toggle="buttons">
        <label class="btn btn-default<?php echo $view === 'list' ? ' active' : ''; ?>">
          <input type="radio" name="view" value="list"<?php echo $view === 'list' ? ' checked' : ''; ?>>
          <i class="fa fa-th-list"></i>
        </label>
        <label class="btn btn-default<?php echo $view === 'grid' ? ' active' : ''; ?>">
          <input type="radio" name="view" value="grid"<?php echo $view === 'grid' ? ' active' : ''; ?>>
          <i class="fa fa-th-large"></i>
        </label>
      </div>
      <?php // Add hidden fields to retain additional GET parameters from the current URL (if any)  ?>
      <?php foreach ($query as $key => $value) { ?>
      <?php if (!in_array($key, array('sort', 'view'))) { ?>
      <input type="hidden" name="<?php echo $this->e($key); ?>" value="<?php echo $this->e($value); ?>">
      <?php } ?>
      <?php } ?>
      <button class="btn btn-default hidden-js"><?php echo $this->text('Go'); ?></button>
    </form>
  </div>
</nav>

