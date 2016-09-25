<?php if (empty($file['url'])) { ?>
<img alt="<?php echo $this->escape($file['title']); ?>" src="<?php echo $file['thumb']; ?>">
<?php } else { ?>
<a href="<?php echo $this->escape($file['url']); ?>"><img alt="<?php echo $this->escape($file['title']); ?>" src="<?php echo $file['thumb']; ?>"></a>
<?php } ?>
