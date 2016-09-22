<nav class="navbar category navbar-default">
<span class="navbar-text navbar-left"><?php echo $this->text('Showing %num from %total', array('%num' => $quantity, '%total' => $total)); ?></span>
<form class="navbar-form navbar-right" onchange="$(this).submit();">
  <span class="form-control-static"><?php echo $this->text('Sort'); ?> </span>
  <select name="sort" class="form-control">
    <option value="price-asc"<?php echo ($sort == 'price-asc') ? ' selected' : ''; ?>>
    <?php echo $this->text('Low prices first'); ?>
    </option>
    <option value="price-desc"<?php echo ($sort == 'price-desc') ? ' selected' : ''; ?>>
    <?php echo $this->text('High prices first'); ?>
    </option>
    <option value="title-asc"<?php echo ($sort == 'title-asc') ? ' selected' : ''; ?>>
    <?php echo $this->text('Title A-Z'); ?>
    </option>
    <option value="title-desc"<?php echo ($sort == 'title-desc') ? ' selected' : ''; ?>>
    <?php echo $this->text('Title Z-A'); ?>
    </option>
  </select>
  <div class="btn-group" data-toggle="buttons">
    <label class="btn btn-default<?php echo ($view == 'list') ? ' active' : ''; ?>">
      <input type="radio" name="view" value="list"<?php echo ($view == 'list') ? ' checked' : ''; ?>><i class="fa fa-th-list"></i>
    </label>
    <label class="btn btn-default<?php echo ($view == 'grid') ? ' active' : ''; ?>">
      <input type="radio" name="view" value="grid"<?php echo ($view == 'grid') ? ' checked' : ''; ?>><i class="fa fa-th-large"></i>
    </label>
  </div>
  <?php foreach($this->query as $key => $value){ ?>
  <?php if(!in_array($key, array('sort', 'view'))) { ?>
  <input type="hidden" name="<?php echo $this->escape($key); ?>" value="<?php echo $this->escape($value); ?>">
  <?php } ?>
  <?php } ?>
  <button class="btn btn-default hidden-js"><?php echo $this->text('Go'); ?></button>
</form>
</nav>