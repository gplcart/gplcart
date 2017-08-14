<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<body<?php echo $this->attributes(array('class' => $_classes)); ?>>
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
            <?php if (!empty($_store['data']['phone'][0])) { ?>
            <i class="fa fa-phone"></i> <?php echo $this->e($_store['data']['phone'][0]); ?>
            <?php } ?>
          </p>
          <?php if (count($_currencies) > 1) { ?>
          <?php $show_currency_selector = false; ?>
          <?php foreach ($_currencies as $currency) { ?>
          <?php if($_currency['code'] !== $currency['code']) { ?>
          <?php $show_currency_selector = true; break; ?>
          <?php } ?>
          <?php } ?>
          <?php if($show_currency_selector) { ?>
          <div class="dropdown pull-left navbar-text">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <?php echo $this->e($_currency['name']); ?> <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <?php foreach ($_currencies as $currency) { ?>
              <?php if($_currency['code'] !== $currency['code']) { ?>
              <li>
                <a rel="nofollow" href="<?php echo $this->url('', array('currency' => $currency['code'])); ?>"><?php echo $this->e($currency['code']); ?></a>
              </li>
              <?php } ?>
              <?php } ?>
            </ul>
          </div>          
          <?php } else { ?>
          <div class="pull-left navbar-text"><?php echo $this->e($_currency['name']); ?></div>
          <?php } ?>
          <?php } ?>
          <?php if(count($_languages) > 1) { ?>
          <div class="dropdown pull-left navbar-text">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <?php if (empty($_languages[$_langcode]['status'])) { ?>
              <?php echo $this->text('select language'); ?>
              <?php } else { ?>
              <?php echo $this->e($_languages[$_langcode]['native_name']); ?>
              <?php } ?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <?php foreach ($_languages as $language) { ?>
              <?php if ($language['code'] !== $_langcode) { ?>
              <li>
                <a href="<?php echo $this->lurl($language['code'], '', $_query); ?>"><?php echo $this->e($language['native_name']); ?></a>
              </li>
              <?php } ?>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
          <p class="nav pull-right">
            <?php if ($_is_logged_in) { ?>
            <a href="<?php echo $this->url("account/$_uid"); ?>">
              <?php echo $this->e($_user['name']); ?>
            </a>
            <i class="fa fa-user"></i>
            <a href="<?php echo $this->url('logout'); ?>">
              <?php echo $this->text('Log out'); ?>
            </a>
            <?php } else { ?>
            <?php echo $this->text('Hello'); ?> <i class="fa fa-user"></i>
            <a rel="nofollow" href="<?php echo $this->url('login'); ?>">
            <?php echo $this->text('Log in'); ?>
            </a>
            <?php } ?>
          </p>
        </div>
      </nav>
    </div>
    <nav class="navbar navbar-default navbar-static-top second">
      <div class="container-fluid">
        <div class="navbar-header">
          <?php if(empty($_store_logo)) { ?>
          <a class="navbar-brand" href="<?php echo $this->e($_base); ?>">
          <?php echo $this->e($_store_title); ?>
          </a>
          <?php } else { ?>
          <a class="navbar-logo" href="<?php echo $this->e($_base); ?>">
            <img class="logo" alt="<?php echo $this->e($_store_title); ?>" title="<?php echo $this->e($_store_title); ?>" src="<?php echo $this->e($_store_logo); ?>">
          </a>
          <?php } ?>
        </div>
        <ul class="nav navbar-nav navbar-right">
          <li class="cart">
            <p class="navbar-btn">
            <a rel="nofollow" class="btn btn-default btn-block" id="cart-link" href="<?php echo $this->url('checkout'); ?>">
              <span class="badge" id="cart-quantity"><?php echo empty($_cart['quantity']) ? 0 : $_cart['quantity']; ?></span>
              <i class="fa fa-shopping-cart"></i>
            </a>
            </p>
          </li>
          <li class="wishlist">
            <?php if (empty($_wishlist)) { ?>
            <p class="navbar-btn">
              <span class="btn btn-default btn-block">
              <span class="badge" id="wishlist-quantity">0</span>
              <i class="fa fa-heart"></i>
              </span>
            </p>
            <?php } else { ?>
            <p class="navbar-btn">
              <a rel="nofollow" id="wishlist-link" class="btn btn-default btn-block" href="<?php echo $this->url('wishlist'); ?>">
                <span class="badge" id="wishlist-quantity"><?php echo count($_wishlist); ?></span>
                <i class="fa fa-heart"></i>
              </a>
            </p>
            <?php } ?>
          </li>
          <li class="compare">
            <?php if (empty($_comparison)) { ?>
            <p class="navbar-btn">
              <span class="btn btn-default btn-block">
                <span class="badge" id="compare-quantity">0</span>
                <i class="fa fa-balance-scale"></i>
              </span>
            </p>
            <?php } else { ?>
            <p class="navbar-btn">
              <a rel="nofollow" class="btn btn-default btn-block" id="compare-link" href="<?php echo $this->url('compare'); ?>">
                <span class="badge" id="compare-quantity">
                  <?php echo count($_comparison); ?>
                </span>
                <i class="fa fa-balance-scale"></i>
              </a>
            </p>
            <?php } ?>
          </li>
        </ul>
        <form class="navbar-form navbar-left search" action="<?php echo $this->url('search'); ?>">
          <div class="input-group">
            <input type="search" class="form-control" autocomplete="off" name="q" value="<?php echo isset($_query['q']) && $_query['q'] !== '' ? $_query['q'] : ''; ?>" placeholder="<?php echo $this->text('Search'); ?>">
            <span class="input-group-btn">
              <button class="btn btn-default" data-block-if-empty="q">
                <i class="fa fa-search"></i>
              </button>
            </span>
          </div>
        </form>
      </div>
    </nav>
    <nav class="navbar navbar-inverse navbar-static-top third">
      <?php if(!empty($_menu)) { ?>
        <div class="navbar-header">
          <p class="navbar-text navbar-hidden-label visible-xs-inline-block"><?php echo $this->text('Categories'); ?></p>
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-third">
            <span class="sr-only"><?php echo $this->text('Toggle navigation'); ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="navbar-collapse collapse" id="navbar-collapse-third">
          <?php echo $_menu; ?>
        </div>
      <?php } ?>
    </nav>
    <?php if (!empty($_breadcrumbs)) { ?>
    <div class="breadcrumb">
      <ol class="breadcrumb">
        <?php foreach ($_breadcrumbs as $item) { ?>
        <?php if (empty($item['url'])) { ?>
        <li><?php echo $this->filter($item['text']); ?></li>
        <?php } else { ?>
        <li><a href="<?php echo $this->e($item['url']); ?>"><?php echo $this->filter($item['text']); ?></a></li>
        <?php } ?>
        <?php } ?>
        <?php if (!empty($_page_title)) { ?>
        <li><h1><?php echo $this->filter($_page_title); ?></h1></li>
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
        <?php if (!empty($_messages)) { ?>
        <div class="row" id="message">
          <div class="col-md-12">
            <?php foreach ($_messages as $type => $strings) { ?>
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
          <?php if(!empty($_store['data']['owner'])) { ?>
          <?php echo $this->e($_store['data']['owner']); ?>
          <?php } ?>
          &copy; 2015 - <?php echo date('Y'); ?> <a href="http://gplcart.com">GPL Cart</a>
        </div>
      </div>
    </div>
  </div>
</body>
