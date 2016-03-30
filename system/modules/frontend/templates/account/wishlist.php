<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><span class="fa fa-user"></span> <?php echo $this->truncate($this->escape($user['name']), 20); ?></h4>
        <p class="list-group-item-text"><?php echo $this->escape($user['email']); ?></p>
      </a>
      <a class="list-group-item active disabled">
        <h4 class="list-group-item-heading"><?php echo $this->text('Wishlist'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage wishlist'); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
    </div>
    <a href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
  </div>
  <div class="col-md-9">

<?php if($wishlist) { ?>
    <table class="table table-condensed">
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo $this->text('Created'); ?></th>
                <th><?php echo $this->text('Amount'); ?></th>
                <th><?php echo $this->text('Status'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($wishlist as $product) { ?>
            <tr>

            </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
<?php echo $this->text('You have no products in your wishlist. <a href="!href">Shop now</a>', array('!href' => $this->url('/'))); ?>
<?php } ?>

  </div>