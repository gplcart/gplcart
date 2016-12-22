<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" class="form-horizontal restore-backup">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <div class="col-md-12">
          <?php echo $this->text('You\'re about to replace all corresponding data with a data from the backup. It cannot be undone. Make sure you have a valid backup of the data to be replaced!'); ?>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-12">
          <button class="btn btn-default" name="save" value="1" onclick="return confirm(GplCart.text('Are you sure?'));">
              <?php echo $this->text('Restore'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
