<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<div class="modal show">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <div id="job-widget-<?php echo $this->e($job['id']); ?>" class="job-widget">
          <?php if (!empty($job['title'])) { ?>
          <div class="title"><?php echo $this->e($job['title']); ?></div>
          <?php } ?>
          <div class="progress">
            <div class="progress-bar active progress-bar-striped" style="width:0">
            </div>
          </div>
          <div class="message">
            <?php if (!empty($job['message']['start'])) { ?>
            <span class="start"><?php echo $this->filter($job['message']['start']); ?></span>
            <?php } ?>
          </div>
        </div>
        <p class="cancel">
          <a href="<?php echo $this->url('', array('cancel_job' => $job['id'])); ?>">
            <?php echo $this->text('Cancel'); ?>
          </a>
        </p>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade in"></div>
<script>
    Gplcart.job();
</script>