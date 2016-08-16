<?php if (empty($content[0])) { ?>
    <?php // Return empty string  ?>
<?php } else { ?>
<div class="modal fade" id="help-summary">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" class="pull-right" data-dismiss="modal">&times;</button>
        <div class="modal-title"><a href="<?php echo $this->url('admin/help'); ?>"><?php echo $this->text('Full manual'); ?></a></div>
      </div>
      <div class="modal-body">
        <?php echo $this->xss($content[0]); ?>
      </div>
      <?php if (!empty($content[1])) { ?>
      <div class="modal-footer">
        <a class="btn btn-default" href="<?php echo $this->url("admin/help/{$file['filename']}"); ?>">
          <?php echo $this->text('Read more'); ?>
        </a>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>