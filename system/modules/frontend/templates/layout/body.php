<body<?php echo $this->attributes(array('class' => $body_classes)); ?>>
  <nav class="navbar navbar-static-top navbar-default first">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only"><?php echo $this->text('Toggle navigation'); ?></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="<?php echo $base; ?>">Project name</a>
      </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
          <li>
            <a rel="nofollow" id="cart-link" href="<?php echo $this->url('checkout'); ?>">
              <?php if (!empty($cart_quantity['total'])) { ?>
              <span class="badge" id="cart-quantity"><?php echo $cart_quantity['total']; ?></span>
              <?php } else { ?>
              <span class="badge" id="cart-quantity" style="display:none;"></span>
              <?php } ?>
              <i class="fa fa-shopping-cart"></i>
            </a>
          </li>
          <li>
            <a rel="nofollow" id="wishlist-link" href="<?php echo $this->url('wishlist'); ?>">
              <?php if (!empty($wishlist_quantity)) { ?>
              <span class="badge" data-selector="wishlist-quantity">
              <?php echo $wishlist_quantity; ?>
              </span>
              <?php } else { ?>
              <span class="badge" data-selector="wishlist-quantity" style="display:none;"></span>
              <?php } ?>
              <i class="fa fa-heart"></i>
            </a>
          </li>
          <li>
            <?php if (!empty($compare_content)) { ?>
            <a rel="nofollow" id="compare-link" href="<?php echo $this->url('compare'); ?>">
              <span class="badge" id="compare-quantity">
                <?php echo count($compare_content); ?>
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
          <?php if ($this->uid) { ?>
          <li class="dropdown">
            <a href="<?php echo $this->url("account/{$this->uid}"); ?>" class="dropdown-toggle " data-toggle="dropdown">
              <i class="fa fa-user"></i></a>
            <ul class="dropdown-menu dropdown-menu-right">
              <li>
                <a href="<?php echo $this->url("account/{$this->uid}"); ?>">
                  <?php echo $this->text('Orders'); ?>
                </a>
              </li>
              <li>
                <a href="<?php echo $this->url("account/{$this->uid}/wishlist"); ?>">
                    <?php echo $this->text('Wishlist'); ?>
                </a>
              </li>
              <li>
                <a href="<?php echo $this->url("account/{$this->uid}/address"); ?>">
                  <?php echo $this->text('Addresses'); ?>
                </a>
              </li>
              <li class="divider"></li>
              <li>
                <a href="<?php echo $this->url('logout'); ?>">
                  <i class="fa fa-sign-out"></i> <?php echo $this->text('Log out'); ?>
                </a>
              </li>
            </ul> 
          </li>
          <?php } else { ?>
          <li>
            <a rel="nofollow" href="<?php echo $this->url('login'); ?>"><i class="fa fa-user"></i></a>
          </li>
          <?php } ?>
        </ul>
        <form class="navbar-form navbar-left search" action="<?php echo $this->url('search'); ?>">
          <div class="input-group">
            <input type="search" pattern="\S+" class="form-control typeahead" autocomplete="off" data-provide="typeahead" name="q" value="<?php echo empty($this->query['q']) ? '' : $this->escape($this->query['q']); ?>" placeholder="<?php echo $this->text('Enter search keyword...'); ?>">
            <i class="fa fa-spinner fa-spin hidden"></i>
            <span class="input-group-btn">
              <button type="submit" class="btn btn-default">
                <i class="fa fa-search"></i>
              </button>
            </span>
          </div>
        </form>
      </div> 
    </div>
  </nav>
  <nav class="navbar navbar-inverse navbar-static-top second">
    <div class="container-fluid">
      <div class="navbar-collapse collapse">
      <?php if (!empty($megamenu)) { ?>
      <?php echo $this->render('category/block/megamenu', array('tree' => $megamenu)); ?>        
      <?php } ?>
      </div>
    </div>
  </nav>
  <?php if (!empty($breadcrumb)) { ?>
  <div class="container-fluid breadcrumb">
    <ol class="breadcrumb">
      <?php foreach ($breadcrumb as $item) { ?>
      <?php if (!empty($item['url'])) { ?>
      <li><a href="<?php echo $this->escape($item['url']); ?>"><?php echo $this->escape($item['text']); ?></a></li>
      <?php } else { ?>
      <li><?php echo $this->escape($item['text']); ?></li>
      <?php } ?>
      <?php } ?>
      <?php if (!empty($page_title)) { ?>
      <li><h1><?php echo $this->xss($page_title); ?></h1></li>
      <?php } ?>
    </ol>
  </div>
  <?php } ?>
  <?php if (!empty($region_top)) { ?>
  <div class="region top"><?php echo $region_top; ?></div>
  <?php } ?>
  <div class="container-fluid main">
    <div class="row">
      <?php if (!empty($region_left)) { ?>
      <div class="col-md-3">
        <div class="region left"><?php echo $region_left; ?></div>
      </div>
      <?php } ?>
      <?php $region_content_class = 'col-md-12'; ?>
      <?php if (!empty($region_left) && empty($region_right)) { ?>
      <?php $region_content_class = 'col-md-9'; ?>
      <?php } ?>
      <?php if (empty($region_left) && !empty($region_right)) { ?>
      <?php $region_content_class = 'col-md-9'; ?>
      <?php } ?>
      <?php if (!empty($region_left) && !empty($region_right)) { ?>
      <?php $region_content_class = 'col-md-6'; ?>
      <?php } ?>
      <div class="<?php echo ($region_content_class); ?>">
        <?php if (!empty($messages)) { ?>
        <div class="row" id="message">
          <div class="col-md-12">
            <?php foreach ($messages as $type => $strings) { ?>
            <div class="alert alert-<?php echo $type; ?> alert-dismissible fade in">
              <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
              <?php foreach ($strings as $string) { ?>
              <?php echo $this->xss($string); ?><br>
              <?php } ?>
            </div>
            <?php } ?>
          </div>
        </div>
        <?php } ?>
        <?php if (!empty($region_content)) { ?>
        <div class="region content"><?php echo $region_content; ?></div>
        <?php if (!empty($region_bottom)) { ?>
        <div class="region bottom"><?php echo $region_bottom; ?></div>
        <?php } ?>
        <?php } ?>
      </div>
      <?php if (!empty($region_right)) { ?>
      <div class="col-md-3">
        <div class="region right"><?php echo $region_right; ?></div>
      </div>
      <?php } ?>
    </div>
  </div>
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <p>
            <?php if (!empty($current_store['data']['owner'])) { ?>
            <?php echo $this->escape($current_store['data']['owner']); ?>
            <?php } ?>
            &copy; <?php echo date('Y'); ?> All Rights Reserved</p>
          <ul class="list-unstyled">
            <?php if (!empty($current_store['data']['address'])) { ?>
            <li><i class="fa fa-map-marker"></i> <?php echo $this->xss($current_store['data']['address']); ?></li>
            <?php } ?>
            <?php if (!empty($current_store['data']['phone'])) { ?>
            <li><i class="fa fa-phone"></i> <?php echo $this->escape(implode(', ', $current_store['data']['phone'])); ?></li>
            <?php } ?>
            <?php if (!empty($current_store['data']['fax'])) { ?>
            <li><i class="fa fa-fax"></i> <?php echo $this->escape(implode(', ', $current_store['data']['fax'])); ?></li>
            <?php } ?>
          </ul>
          <p class="small text-muted">Powered by <a href="http://gplcart.com">GPL Cart</a></p>
        </div>
        <div class="col-md-4">
          <ul class="list-unstyled">
            <li>
              <a rel="nofollow" href="<?php echo $this->url('about.html'); ?>"><?php echo $this->text('About us'); ?></a>
            </li>
            <li>
              <a rel="nofollow" href="<?php echo $this->url('contact.html'); ?>"><?php echo $this->text('Contact us'); ?></a>
            </li>
            <li>
              <a rel="nofollow" href="<?php echo $this->url('terms.html'); ?>"><?php echo $this->text('Terms and conditions'); ?></a>
            </li>
            <li>
              <a rel="nofollow" href="<?php echo $this->url('faq.html'); ?>"><?php echo $this->text('Questions and answers'); ?></a>
            </li>
          </ul>          
        </div>
        <div class="col-md-2">
          <?php if (!empty($current_store['data']['social'])) { ?>
          <?php foreach ($current_store['data']['social'] as $network => $network_url) { ?>
          <a rel="nofollow" target="_blank" href="<?php echo $this->escape($network_url); ?>">       
            <span class="fa-stack fa-lg">
              <i class="fa fa-square-o fa-stack-2x"></i>
              <i class="fa fa-<?php echo $this->escape($network); ?> fa-stack-1x"></i>
            </span>
          </a>
          <?php } ?>
          <?php } ?>
        </div>
      </div>
    </div>
  </footer>
</body>