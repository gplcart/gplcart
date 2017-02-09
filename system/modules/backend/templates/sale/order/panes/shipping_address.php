<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Shipping address'); ?></div>
  <div class="panel-body">
    <div class="row">
      <div class="col-md-6">
        <?php if (!empty($order['address_translated']['shipping'])) { ?>
        <table class="table table-condensed">
          <?php foreach ($order['address_translated']['shipping'] as $key => $value) { ?>
          <tr>
            <td><?php echo $this->escape($key); ?></td>
            <td><?php echo $this->escape($value); ?></td>
          </tr>
          <?php } ?>
        </table>
        <?php } ?>
      </div>
      <div class="col-md-6">
        <div class="embed-responsive embed-responsive-4by3">
          <div id="map-container" class="embed-responsive-item text-muted">
            <?php if(!$this->config('gapi_browser_key')) { ?>
            <?php echo $this->text('Google Map API key is not set'); ?>
            <?php if($this->access('settings')) { ?>
            <a href="<?php echo $this->url('admin/settings/common'); ?>"><?php echo mb_strtolower($this->text('Edit')); ?></a>
            <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>