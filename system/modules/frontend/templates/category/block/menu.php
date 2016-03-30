<?php if(!empty($tree)) { ?>
<ul class="list-group" id="sidebar-menu">
<?php foreach ($tree as $item) { ?>
  <li class="list-group-item depth-<?php echo $item['depth']; ?><?php echo empty($item['active']) ? '' : ' active'; ?>">
  <?php echo $item['indentation']; ?>
  <?php if (empty($item['active'])) { ?>
  <a title="<?php echo $item['title']; ?>" href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
  <?php } else { ?>
  <?php echo $item['title']; ?>
  <?php } ?>
  </li>
<?php } ?>
</ul>
<?php } ?>