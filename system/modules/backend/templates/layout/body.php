<body class="<?php echo $this->escape(implode(' ', $body_classes)); ?>">
  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only"><?php echo $this->text('Toggle navigation'); ?></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <ul class="nav navbar-nav navbar-left goto">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle navbar-brand" data-toggle="dropdown">
              GPL Cart <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <?php if ($this->access('dashboard') && !$this->url->isDashboard()) { ?>
              <li><a href="<?php echo $this->url('admin'); ?>">
              <?php echo $this->text('Dashboard'); ?></a>
              </li>
              <li class="divider"></li>
              <?php } ?>
              <?php foreach ($store_list as $store) { ?>
              <li>
                <a href="<?php echo $this->escape("{$store['scheme']}{$store['domain']}/{$store['basepath']}"); ?>">
                  <i class="fa fa-external-link"></i> <?php echo $this->escape($store['name']); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </li>
        </ul>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-left">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->text('Content'); ?></a>
            <ul class="dropdown-menu">
              <?php if ($this->access('product')) { ?>
              <li><a href="<?php echo $this->url('admin/content/product'); ?>"><?php echo $this->text('Products'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('category_group')) { ?>
              <li><a href="<?php echo $this->url('admin/content/category/group'); ?>"><?php echo $this->text('Categories'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('review')) { ?>
              <li><a href="<?php echo $this->url('admin/content/review'); ?>"><?php echo $this->text('Reviews'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('page')) { ?>
              <li><a href="<?php echo $this->url('admin/content/page'); ?>"><?php echo $this->text('Pages'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('file')) { ?>
              <li><a href="<?php echo $this->url('admin/content/file'); ?>"><?php echo $this->text('Files'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('product_class')) { ?>
              <li><a href="<?php echo $this->url('admin/content/product/class'); ?>"><?php echo $this->text('Product classes'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('field')) { ?>
              <li><a href="<?php echo $this->url('admin/content/field'); ?>"><?php echo $this->text('Fields'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('alias')) { ?>
              <li><a href="<?php echo $this->url('admin/content/alias'); ?>"><?php echo $this->text('Aliases'); ?></a></li>
              <?php } ?>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->text('Sales'); ?></a>
            <ul class="dropdown-menu">
              <?php if ($this->access('order')) { ?>
              <li><a href="<?php echo $this->url('admin/sale/order'); ?>"><?php echo $this->text('Orders'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('price_rule')) { ?>
              <li><a href="<?php echo $this->url('admin/sale/price'); ?>"><?php echo $this->text('Prices'); ?></a></li>
              <?php } ?>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->text('Reports'); ?></a>
            <ul class="dropdown-menu">
              <?php if ($this->access('report_system')) { ?>
              <li><a href="<?php echo $this->url('admin/report/system'); ?>"><?php echo $this->text('Events'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('report_ga')) { ?>
              <li><a href="<?php echo $this->url('admin/report/ga'); ?>"><?php echo $this->text('Analytics'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('notification')) { ?>
              <li><a href="<?php echo $this->url('admin/report/notification'); ?>"><?php echo $this->text('Notifications'); ?></a></li>
              <?php } ?>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->text('Users'); ?></a>
            <ul class="dropdown-menu">
              <?php if ($this->access('user')) { ?>
              <li><a href="<?php echo $this->url('admin/user'); ?>"><?php echo $this->text('Users'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('user_role')) { ?>
              <li><a href="<?php echo $this->url('admin/user/role'); ?>"><?php echo $this->text('Roles'); ?></a></li>
              <?php } ?>
            </ul>
          </li>
          <?php if ($this->access('module') || $this->access('marketplace')) { ?>
          <li>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->text('Modules'); ?></a>
            <ul class="dropdown-menu">
              <?php if ($this->access('module')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/module'); ?>"><?php echo $this->text('Local'); ?></a>
              </li>
              <?php } ?>
              <?php if ($this->access('marketplace')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/module/marketplace'); ?>"><?php echo $this->text('Marketplace'); ?></a>
              </li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <?php echo $this->text('Tools'); ?></a>
            <ul class="dropdown-menu">
              <?php if ($this->access('import')) { ?>
              <li><a href="<?php echo $this->url('admin/tool/import'); ?>"><?php echo $this->text('Import'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('export')) { ?>
              <li><a href="<?php echo $this->url('admin/tool/export'); ?>"><?php echo $this->text('Export'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('cron')) { ?>
              <li><a href="<?php echo $this->url('admin/tool/cron'); ?>"><?php echo $this->text('Cron'); ?></a></li>
              <?php } ?>
              <?php if ($this->access('search_edit')) { ?>
              <li><a href="<?php echo $this->url('admin/tool/search'); ?>"><?php echo $this->text('Search'); ?></a></li>
              <?php } ?>
              <?php if ($this->isSuperadmin()) { ?>
              <li><a href="<?php echo $this->url('admin/tool/demo'); ?>"><?php echo $this->text('Demo'); ?></a></li>
              <?php } ?>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->text('Settings'); ?></a>
            <ul class="dropdown-menu">
              <?php if ($this->isSuperadmin()) { ?>
              <li>
                <a href="<?php echo $this->url('admin/settings/common'); ?>"><?php echo $this->text('Common'); ?></a>
              </li>
              <?php } ?>
              <?php if ($this->access('store')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/settings/store'); ?>"><?php echo $this->text('Store'); ?></a>
              </li>
              <?php } ?>
              <?php if ($this->access('imagestyle')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/settings/imagestyle'); ?>"><?php echo $this->text('Images'); ?></a>
              </li>
              <?php } ?>
              <?php if ($this->access('country')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/settings/country'); ?>"><?php echo $this->text('Countries'); ?></a>
              </li>
              <?php } ?>
              <?php if ($this->access('language')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/settings/language'); ?>"><?php echo $this->text('Languages'); ?></a>
              </li>
              <?php } ?>
              <?php if ($this->access('currency')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/settings/currency'); ?>"><?php echo $this->text('Currencies'); ?></a>
              </li>
              <?php } ?>
            </ul>
          </li>
        </ul>
        <form class="navbar-form navbar-left hidden-sm hidden-xs" id="search-form" action="<?php echo $this->url('admin/search'); ?>">
          <div class="input-group">
            <input class="form-control" id="search-autocomplete" name="q" placeholder="<?php echo $this->text('Search'); ?>">
            <span class="input-group-btn">
              <select class="form-control" name="search_id">
                <?php foreach ($search_handlers as $search_handler_id => $search_handler_data) { ?>
                <option value="<?php echo $search_handler_id; ?>">
                <?php echo $this->escape($search_handler_data['name']); ?>
                </option>
                <?php } ?>
              </select>
            </span>
            <span class="input-group-btn">
              <button class="btn btn-default"><i class="fa fa-search"></i></button>
            </span>
          </div>
        </form>
        <ul class="nav navbar-nav navbar-right right-links hidden-sm hidden-xs">
          <?php if ($this->access('notification') && $notifications) { ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle " data-toggle="dropdown">
              <i class="fa notification fa-exclamation-circle"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <?php foreach ($notifications as $notification_id => $notification) { ?>
              <li class="text-<?php echo $this->escape($notification['severity']); ?>">
                <a href="<?php echo $this->url("admin/report/notification#notification-$notification_id"); ?>">
                  <?php echo $this->escape($notification['text']); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>
          <li><a href="<?php echo $this->url('admin/help'); ?>"><i class="fa fa-question-circle"></i></a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-user"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <li>
                <a href="<?php echo $this->url("account/{$this->uid}"); ?>"><?php echo $this->text('Account'); ?></a>
              </li>
              <li class="divider"></li>
              <li>
                <a href="<?php echo $this->url('logout'); ?>"><i class="fa fa-sign-out"></i> <?php echo $this->text('Log out'); ?></a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <?php if ($page_title || $page_description || $breadcrumb) { ?>
  <div class="container-fluid content-header">
    <div class="row">
      <div class="col-md-8">
        <div class="header-wrapper pull-left">
          <h1 class="h3 pull-left"><?php echo $page_title; ?></h1><?php echo $help; ?>
        </div>
        <?php if ($page_description) { ?>
        <span class="small"><?php echo $page_description; ?></span>
        <?php } ?>
      </div>
      <div class="col-md-4 text-right">
        <?php if ($breadcrumb) { ?>
        <ol class="breadcrumb pull-right">
          <?php foreach ($breadcrumb as $item) { ?>
          <li><a href="<?php echo $item['url']; ?>"><?php echo $item['text']; ?></a></li>
          <?php } ?>
        </ol>
        <?php } ?>
      </div>
    </div>
  </div>
  <?php } ?>
  <div class="container-fluid content">
    <?php if (!empty($messages)) { ?>
    <div class="row" id="message">
      <div class="col-md-12">
        <?php foreach ($messages as $type => $strings) { ?>
        <div class="alert alert-<?php echo $type; ?> alert-dismissible fade in" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
          <?php foreach ($strings as $string) { ?>
          <?php echo $string; ?><br>
          <?php } ?>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
    <div class="row">
      <div class="col-md-12">
      <?php if (!empty($region_content)) { ?>
      <?php echo $region_content; ?>
      <?php } ?>
      </div>
    </div>
  </div>
  <?php if (!empty($region_bottom)) { ?>
  <?php echo $region_bottom; ?>
  <?php } ?>
</body>