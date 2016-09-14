<div class="row">
  <div class="col-md-5">
    <div class="row">
      <div class="col-md-12">
        <?php echo $images; ?>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="row">
      <div class="col-md-6">
        <div id="sku" class="small"># <?php echo $this->escape($product['sku']); ?></div>
      </div>
      <div class="col-md-6 text-right">
        <?php echo $share; ?>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <h1 class="h4"><?php echo $this->escape($product['title']); ?></h1>
      </div>
    </div>
    
    <div class="panel panel-default">
      
      <div class="panel-body">
    
    <div class="row">
      <div class="col-md-12">
        <div id="price" class="h3"><?php echo $product['price_formatted']; ?></div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12"><?php echo $rating; ?></div>
    </div>
    <div class="row">
      <div class="col-md-12"><?php echo $cart_form; ?></div>
    </div>
        
      </div>
  
    </div>
  
  
  </div>
</div>
<?php if(!empty($product['description'])) { ?>
<div class="row">
  <div class="col-md-12">
      <div class="margin-bottom-20"><?php echo $this->xss($product['description']); ?></div>
      
  </div>
</div>
<?php } ?>
<?php if(!empty($related)) { ?>
<?php echo $related; ?>
<?php } ?>
<?php if(!empty($recent)) { ?>
<?php echo $recent; ?>
<?php } ?>
<?php if(!empty($reviews)) { ?>
<?php echo $reviews; ?>
<?php } ?>

