<?php if ($admin) { ?>
<div class="panel panel-default">
  <div class="panel-body">
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Customer'); ?></label>
      <div class="col-md-2">
        <?php echo $user['name']; ?> (<?php echo $user['email']; ?>)
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
      <div class="col-md-2">
        <select class="form-control" name="order[status]">
          <?php foreach ($statuses as $status_id => $status_name) { ?>
          <option value="<?php echo $this->escape($status_id); ?>"<?php echo (isset($order['status']) && $order['status'] == $status_id) ? ' selected' : ''; ?>>
          <?php echo $this->escape($status_name); ?>
          </option>
          <?php } ?>
        </select>
      </div>
    </div>
    <?php if(!empty($order['user_id'])) { ?>
    <div class="form-group required<?php echo isset($this->errors['log']) ? ' has-error' : ''; ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Log message'); ?></label>
      <div class="col-md-4">
        <textarea class="form-control" name="order[log]"><?php echo isset($order['log']) ? $this->escape($order['log']) : ''; ?></textarea>
        <div class="help-block">
          <?php if(isset($this->errors['log'])) { ?>
          <?php echo $this->errors['log']; ?>
          <?php } ?>
          <div class="text-muted">
            <?php echo $this->text('Enter a short clear explanation of the update you are making'); ?>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
</div>
<?php } ?>

