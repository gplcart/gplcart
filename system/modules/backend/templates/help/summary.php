<?php if(empty($content[1])) { ?>
<span class="help link">
  <a href="<?php echo $this->url("admin/help/{$file['filename']}"); ?>">
    <i class="fa fa-question-circle"></i>
  </a>
</span>
<?php } else { ?>
<span class="help summary">
  <a href="<?php echo $this->url("admin/help/{$file['filename']}"); ?>">
    <i class="fa fa-question-circle"></i>
  </a>
  <span class="summary hidden">
    <?php echo $this->xss($content[0]); ?>
  </span>
</span>
<?php } ?>


