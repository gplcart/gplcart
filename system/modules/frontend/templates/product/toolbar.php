<?php if ($this->access('product') || $this->access('product_edit')) {
    ?>
<div class="navbar navbar-default navbar-fixed-bottom">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".bottom-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>
    <div class="navbar-collapse collapse bottom-collapse">
      <?php if ($this->access('product_edit')) {
    ?>
      <p class="navbar-text">
        <a href="<?php echo $this->url("admin/content/product/edit/{$product['product_id']}", array('target' => $this->url()));
    ?>">
        <?php echo $this->text('Edit');
    ?>
        </a>
      </p>
      <?php 
}
    ?>
    </div>
  </div>
</div>
<?php 
} ?>