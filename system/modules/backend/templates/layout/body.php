<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<body class="<?php echo $this->escape(implode(' ', $body_classes)); ?>">
  <nav class="navbar navbar-inverse navbar-fixed-top hidden-print">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only"><?php echo $this->text('Toggle navigation'); ?></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <?php if (!$this->url->isDashboard()) { ?>
        <a class="navbar-brand" href="<?php echo $this->url('admin'); ?>" title="<?php echo $this->text('Dashboard'); ?>">
        GPL Cart
        </a>
        <?php } else { ?>
        <span class="navbar-brand">GPL Cart</span>
        <?php } ?>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <?php echo $admin_menu; ?>
        <ul class="nav navbar-nav navbar-right right-links hidden-sm hidden-xs">
          <?php if($this->access('cli')) { ?>
          <li>
            <a href="#" data-terminal="true"><i class="fa fa-terminal"></i></a>
          </li>
          <?php } ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle " data-toggle="dropdown">
              <i class="fa fa-globe"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <?php foreach ($store_list as $store) { ?>
              <li>
                <a target="_blank" href="<?php echo $this->escape("{$this->scheme}{$store['domain']}/{$store['basepath']}"); ?>">
                  <i class="fa fa-external-link"></i> <?php echo $this->escape($store['name']); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </li>
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
  <?php if ($page_title || $breadcrumb) { ?>
  <div class="container-fluid content-header hidden-print">
    <div class="row">
      <div class="col-md-12">
        <?php if (!empty($breadcrumb)) { ?>
        <ol class="breadcrumb">
          <?php foreach ($breadcrumb as $item) { ?>
          <?php if(empty($item['url'])) { ?>
          <li><?php echo $this->xss($item['text']); ?></li>
          <?php } else { ?>
          <li><a href="<?php echo $this->escape($item['url']); ?>"><?php echo $this->xss($item['text']); ?></a></li>
          <?php } ?>
          <?php } ?>
          <?php if(!empty($page_title)) { ?>
          <li><?php echo $this->xss($page_title); ?></li>
          <?php } ?>
        </ol>
        <?php } ?>
      </div>
    </div>
  </div>
  <?php } ?>
  <div class="container-fluid content">
    <?php if (!empty($messages)) { ?>
    <div class="row hidden-print" id="message">
      <div class="col-md-12">
        <?php foreach ($messages as $type => $strings) { ?>
        <div class="alert alert-<?php echo $type; ?> alert-dismissible fade in" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
          <?php foreach ($strings as $string) { ?>
          <?php echo $this->xss($string); ?><br>
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