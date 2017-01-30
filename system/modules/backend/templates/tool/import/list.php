<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-body">
    <?php foreach ($operations as $id => $operation) { ?>
    <form method="post" enctype="multipart/form-data" id="import-csv-<?php echo $id; ?>" class="form-horizontal" onsubmit="return confirm(GplCart.text('Are you sure?'));">
      <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
      <div class="form-group<?php echo $this->error("$id.file", ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->escape($operation['name']); ?></label>
        <div class="col-md-4">
          <input type="file" class="form-control" name="file" accept=".csv">
          <div class="help-block">
            <?php echo $this->error("$id.file"); ?>
            <?php if (!empty($operation['description'])) { ?>
            <div class="text-muted">
              <?php echo $this->xss($operation['description']); ?>
            </div>
            <?php } ?>
            <?php if (!empty($operation['csv']['template'])) { ?>
            <a href="<?php echo $this->url(false, array('download_template' => $id)); ?>">
              <?php echo $this->text('Download template'); ?>
            </a>
            <?php } ?>
          </div>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
          <button class="btn btn-default import" name="import" value="<?php echo $id; ?>">
            <?php echo $this->text('Import'); ?>
          </button>
          </div>
        </div>
      </div>
    </form>
    <?php } ?>
  </div>
</div>
<?php if (!empty($job)) { ?>
<?php echo $job; ?>
<?php } ?>