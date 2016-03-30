<div class="row">
  <div class="col-md-12">
    <?php if ($notifications_list) { ?>
    <?php foreach ($notifications_list as $notification_id => $messages) { ?>
    <div id="notification-<?php echo $notification_id; ?>">
      <?php foreach($messages as $index => $message) { ?>
      <div id="notification-<?php echo $notification_id; ?>-<?php echo $index; ?>" class="alert alert-dismissible alert-<?php echo $message['severity']; ?>">
        <a class="close" data-dismiss="alert" href="<?php echo $this->url(false, array(
            'notification_id' => $notification_id, 'index' => $index, 'clear' => 1)); ?>">&times;</a>
        <?php echo $this->xss($message['message']); ?>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
    <?php } else { ?>
    <?php echo $this->text('You have no notifications yet'); ?>
    <?php } ?>
  </div>
</div>
