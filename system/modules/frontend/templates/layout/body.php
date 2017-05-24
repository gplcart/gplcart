<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<body<?php echo $this->attributes(array('class' => $body_classes)); ?>>
  <div class="container-fluid wrapper">
    <div class="row">
      <nav class="navbar navbar-default navbar-static-top first">
        <div class="container-fluid">
          <ul class="list-inline pull-left">
            <li>
              <a rel="nofollow" href="<?php echo $this->url('page/1'); ?>"><?php echo $this->text('Contact'); ?></a>
            </li>
            <li>
              <a rel="nofollow" href="<?php echo $this->url('page/2'); ?>"><?php echo $this->text('Help'); ?></a>
            </li>
          </ul>
          <p class="nav pull-left navbar-text">
            <?php if ($this->store('data.phone.0')) { ?>
            <i class="fa fa-phone"></i> <?php echo $this->e($this->store('data.phone.0')); ?>
            <?php } ?>
          </p>
          <?php if (!empty($currencies)) { ?>
          <div class="dropdown pull-left navbar-text">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <?php echo $currency_code; ?> <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <?php foreach ($currencies as $currency) { ?>
              <li>
                <a rel="nofollow" href="<?php echo $this->url('', array('currency' => $currency['code'])); ?>"><?php echo $this->e($currency['code']); ?></a>
              </li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
          <?php if (!empty($languages)) { ?>
              <div class="dropdown pull-left navbar-text">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <?php if (empty($languages[$langcode]['native_name'])) { ?>
                  <?php echo $this->text('select language'); ?>
                  <?php } else { ?>
                  <?php echo $this->e($languages[$langcode]['native_name']); ?>
                  <?php } ?>
                  <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                  <?php foreach ($languages as $language) { ?>
                  <?php if ($language['code'] !== $langcode) { ?>
                  <li>
                    <a href="<?php echo $this->urll($language['code'], '', $query); ?>"><?php echo $this->e($language['native_name']); ?></a>
                  </li>
                  <?php } ?>
                  <?php } ?>
                </ul>
              </div>
          <?php } ?>
          <p class="nav pull-right">
            <?php if ($this->user('user_id')) { ?>
            <a href="<?php echo $this->url('account/' . $this->user('user_id')); ?>">
              <i class="fa fa-user"></i>
            </a>
            <a href="<?php echo $this->url('logout'); ?>">
              <?php echo $this->text('Log out'); ?>
            </a>
            <?php } else { ?>
            <?php echo $this->text('Hello'); ?> <i class="fa fa-user"></i>
            <a rel="nofollow" href="<?php echo $this->url('login'); ?>"><?php echo $this->text('Log in'); ?></a>
            <?php } ?>
          </p>
        </div>
      </nav>
    </div>
    <nav class="navbar navbar-default navbar-static-top second">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only"><?php echo $this->text('Toggle navigation'); ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo $this->e($base); ?>">
            <?php if(empty($store_logo)) { ?>
            <?php echo $this->e($store_title); ?>
            <?php } else { ?>
            <img class="logo" alt="<?php echo $this->e($store_title); ?>" title="<?php echo $this->e($store_title); ?>" src="<?php echo $this->e($store_logo); ?>">
            <?php } ?>
          </a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <?php if (!$this->path('checkout')) { ?>
            <li class="cart">
              <a rel="nofollow" id="cart-link" href="<?php echo $this->url('checkout'); ?>">
                <?php if (!empty($cart['quantity'])) { ?>
                <span class="badge" id="cart-quantity"><?php echo $cart['quantity']; ?></span>
                <?php } else { ?>
                <span class="badge" id="cart-quantity" style="display:none;"></span>
                <?php } ?>
                <i class="fa fa-shopping-cart"></i>
              </a>
            </li>
            <?php } ?>
            <li class="wishlist">
              <a rel="nofollow" id="wishlist-link" href="<?php echo $this->url('wishlist'); ?>">
                <?php if (!empty($wishlist)) { ?>
                <span class="badge" id="wishlist-quantity">
                  <?php echo count($wishlist); ?>
                </span>
                <i class="fa fa-heart"></i>
                <?php } else { ?>
                <span class="badge" id="wishlist-quantity"></span>
                <i class="fa fa-heart"></i>
                <?php } ?>
              </a>
            </li>
            <li class="compare">
              <?php if (!empty($comparison)) { ?>
              <a rel="nofollow" id="compare-link" href="<?php echo $this->url('compare'); ?>">
                <span class="badge" id="compare-quantity">
                  <?php echo count($comparison); ?>
                </span>
                <i class="fa fa-balance-scale"></i>
              </a>
              <?php } else { ?>
              <span class="navbar-text">
                <span class="badge" id="compare-quantity" style="display:none;"></span>
                <i class="fa fa-balance-scale"></i>
              </span>
              <?php } ?>
            </li>
          </ul>
          <form class="navbar-form navbar-left search" action="<?php echo $this->url('search'); ?>">
            <div class="input-group">
              <input type="search" class="form-control" autocomplete="off" name="q" value="<?php echo $this->getQuery('q', ''); ?>" placeholder="<?php echo $this->text('Search'); ?>">
              <i class="fa fa-spinner fa-spin hidden"></i>
              <span class="input-group-btn">
                <button class="btn btn-default" data-block-if-empty="q">
                  <i class="fa fa-search"></i>
                </button>
              </span>
            </div>
          </form>
        </div>
      </div>
    </nav>
    <?php if(!empty($category_menu)) { ?>
    <nav class="navbar navbar-inverse navbar-static-top third">
      <?php echo $category_menu; ?>
    </nav>
    <?php } ?>
    <?php if ($breadcrumb) { ?>
    <div class="breadcrumb">
      <ol class="breadcrumb">
        <?php foreach ($breadcrumb as $item) { ?>
        <?php if (empty($item['url'])) { ?>
        <li><?php echo $this->filter($item['text']); ?></li>
        <?php } else { ?>
        <li><a href="<?php echo $this->e($item['url']); ?>"><?php echo $this->filter($item['text']); ?></a></li>
        <?php } ?>
        <?php } ?>
        <?php if (!empty($page_title)) { ?>
        <li><h1><?php echo $this->filter($page_title); ?></h1></li>
        <?php } ?>
      </ol>
    </div>
    <?php } ?>
    <?php if (!empty($region_top)) { ?>
    <div class="row">
      <div class="col-md-12">
        <div class="region top">
          <?php echo $region_top; ?>
        </div>
      </div>
    </div>
    <?php } ?>
    <div class="row main">
      <?php if (!empty($region_left)) { ?>
      <div class="col-md-2">
        <div class="region left"><?php echo $region_left; ?></div>
      </div>
      <?php } ?>
      <?php
      $region_content_class = 'col-md-12';
      if (!empty($region_left) && empty($region_right)) {
          $region_content_class = 'col-md-10';
      } else if (empty($region_left) && !empty($region_right)) {
          $region_content_class = 'col-md-10';
      } else if (!empty($region_left) && !empty($region_right)) {
          $region_content_class = 'col-md-8';
      }
      ?>
      <div class="<?php echo $region_content_class; ?>">
        <?php if (!empty($messages)) { ?>
        <div class="row" id="message">
          <div class="col-md-12">
            <?php foreach ($messages as $type => $strings) { ?>
            <div class="alert alert-<?php echo $this->e($type); ?> alert-dismissible fade in">
              <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
              <?php foreach ($strings as $string) { ?>
              <?php echo $this->filter($string); ?><br>
              <?php } ?>
            </div>
            <?php } ?>
          </div>
        </div>
        <?php } ?>
        <?php if (!empty($region_content)) { ?>
        <div class="region content"><?php echo $region_content; ?></div>
        <?php } ?>
        <?php if (!empty($region_bottom)) { ?>
        <div class="region bottom"><?php echo $region_bottom; ?></div>
        <?php } ?>
      </div>
      <?php if (!empty($region_right)) { ?>
      <div class="col-md-2">
        <div class="region right"><?php echo $region_right; ?></div>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="footer">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
            <?php echo $this->e($this->store('data.owner')); ?>
            &copy; 2015 - <?php echo date('Y'); ?> <a href="http://gplcart.com">GPL Cart</a>
        </div>
      </div>
    </div>
  </div>
</body>
