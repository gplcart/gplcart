<?php if ($tree) { ?>
<?php $depth = 0; ?>
<?php $num_at_depth = 0; ?>
<ul class="nav navbar-nav">
    <li class="dropdown full-width">
    <?php foreach ($tree as $category) { ?>
    <?php $diffdepth = 0; ?>
    <?php if ($category['depth'] > $depth) { ?>
    <ul class="<?php echo $category['depth'] == 1 ? 'dropdown-menu ' : ''; ?>depth-<?php echo $category['depth']; ?>">
    <li class="depth-<?php echo $category['depth']; ?>">
    <?php $depth = $category['depth']; ?>
    <?php $num_at_depth = 0; ?>
    <?php } ?>
    <?php if ($category['depth'] < $depth) { ?>
    <?php $diffdepth = $depth - $category['depth']; ?>
    <?php while ($diffdepth > 0) { ?>
    </li></ul>
    <?php $diffdepth--; ?>
    <?php } ?>
    <?php $depth = $category['depth']; ?>
    <?php } ?>
    <?php if (($category['depth'] == $depth) && ($num_at_depth > 0)) { ?>
    </li><li class="depth-<?php echo $depth; ?><?php echo ($depth == 0) ? ' dropdown full-width' : ''; ?>">
    <?php } ?>
    <a href="<?php echo $this->escape($category['url']); ?>" class="dropdown-toggle"<?php echo $category['depth'] == 0 ? ' data-toggle="dropdown"' : ''; ?>>
      <?php echo $this->escape($category['title']); ?>
    </a>
    <?php $num_at_depth++; ?>
    <?php } ?>
    </li>
    </ul>
<?php } ?>