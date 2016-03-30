    <div class="panel panel-default">
      <div class="panel-heading"><?php echo $this->text('Customer'); ?></div>
      <div class="panel-body">
        <div class="row">
          <div class="col-md-12">
        <?php if(isset($user['user_id'])) { ?>
          <table class="table table-condensed">
            
              <tr>
                <td><?php echo $this->text('ID'); ?></td>
                <td>
                  <a href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>">
                    <?php echo $this->escape($user['user_id']); ?>
                  </a>
                </td>
            </tr>
            
              <tr>
                <td><?php echo $this->text('E-mail'); ?></td>
                <td><?php echo $this->escape($user['email']); ?></td>
            </tr>
            
              <tr>
                <td><?php echo $this->text('Name'); ?></td>
                <td><?php echo $this->escape($user['name']); ?></td>
            </tr>
            
              <tr>
                <td><?php echo $this->text('Role'); ?></td>
                <td>
                  <?php echo $this->escape($user['role_name']); ?>
                  <?php if(empty($user['role_name'])) { ?>
                  <?php echo $this->text('Unknown'); ?>
                  <?php } else { ?>
                  <?php echo $this->escape($user['role_name']); ?>
                  <?php } ?>
                </td>
            </tr>
            
              <tr>
                <td><?php echo $this->text('Created'); ?></td>
                <td><?php echo $this->date($user['created']); ?></td>
            </tr>
            
              <tr>
                <td><?php echo $this->text('Status'); ?></td>
                <td>
                  <?php if(empty($user['status'])) { ?>
                  <span class="text-danger"><?php echo $this->text('Disabled'); ?></span>
                  <?php } else { ?>
                  <span class="text-success"><?php echo $this->text('Enabled'); ?></span>
                  <?php } ?>
                </td>
            </tr>
            
              <tr>
                <td><?php echo $this->text('Orders placed'); ?></td>
                <td>
                  <a href="<?php echo $this->url('admin/sale/order', array('user_id' => $order['user_id'])); ?>"><?php echo $this->escape($placed); ?></a>
                </td>
            </tr>
            
         
            
        </table>
        
        <?php } ?>
            
          </div>
        </div>
            
            
      </div>
    </div> 