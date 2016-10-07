<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Summary'); ?></div>
  <div class="panel-body">
    <table class="table table-condensed">
      <tr>
        <td><?php echo $this->text('Order ID'); ?></td>
        <td><?php echo $this->escape($order['order_id']); ?></td>
      </tr>
      <tr>
        <td><?php echo $this->text('Created'); ?></td>
        <td><?php echo $this->date($order['created']); ?></td>
      </tr>
      <?php if (!empty($order['modified'])) { ?>
      <tr>
        <td><?php echo $this->text('Last modified'); ?></td>
        <td><?php echo $this->date($order['modified']); ?></td>
      </tr>
      <?php } ?>
      <tr>
        <td><?php echo $this->text('Total'); ?></td>
        <td><?php echo $this->escape($order['total_formatted']); ?></td>
      </tr>
      <tr>
        <td><?php echo $this->text('Store'); ?></td>
        <td>
          <?php if (empty($order['store_name'])) { ?>
          <?php echo $this->text('Unknown'); ?>
          <?php } else { ?>
          <?php echo $this->escape($order['store_name']); ?>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $this->text('Creator'); ?></td>
        <td><?php echo $this->escape($order['creator_formatted']); ?></td>
      </tr>
      <tr>
        <td><?php echo $this->text('Status'); ?></td>
        <td>
          <?php if (isset($statuses[$order['status']])) { ?>
          <?php echo $this->escape($statuses[$order['status']]); ?>
          <?php } else { ?>
          <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $this->text('IP'); ?></td>
        <td>
          <?php if (empty($order['data']['user']['ip'])) { ?>
          <?php echo $this->text('Unknown'); ?>
          <?php } else { ?>
          <?php echo $this->escape($order['data']['user']['ip']); ?>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $this->text('User agent'); ?></td>
        <td>
          <?php if (empty($order['data']['user']['agent'])) { ?>
          <?php echo $this->text('Unknown'); ?>
          <?php } else { ?>
          <?php echo $this->escape($order['data']['user']['agent']); ?>
          <?php } ?>
        </td>
      </tr>
    </table>
  </div>
</div>