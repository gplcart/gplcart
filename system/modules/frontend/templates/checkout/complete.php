<div class="panel panel-default complete">
  <div class="panel-body">
    <?php echo $complete_message; ?>
    <?php if (!empty($templates)) { ?>
    <?php foreach ($templates as $template) { ?>
    <?php echo $template; ?>
    <?php } ?>
    <?php } ?>
  </div>
</div>