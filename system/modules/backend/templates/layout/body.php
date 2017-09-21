<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<body<?php echo $this->attributes(array('class' => $_classes)); ?>>
  <nav class="navbar navbar-inverse navbar-static-top hidden-print admin-menu">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
          <span class="sr-only"><?php echo $this->text('Toggle navigation'); ?></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <?php if ($this->path('^admin$')) { ?>
        <span class="navbar-brand">GPLcart</span>
        <?php } else { ?>
        <a class="navbar-brand" href="<?php echo $this->url('admin'); ?>" title="<?php echo $this->text('Dashboard'); ?>">
        GPLCart
        </a>
        <?php } ?>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <?php if(!empty($_menu)) { ?>
        <?php echo $_menu; ?>
        <?php } ?>
        <ul class="nav navbar-nav navbar-right right-links hidden-sm hidden-xs">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle " data-toggle="dropdown">
              <i class="fa fa-globe"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <?php foreach ($_stores as $store) { ?>
              <li>
                <a target="_blank" href="<?php echo $this->e("http://{$store['domain']}/{$store['basepath']}"); ?>">
                  <i class="fa fa-external-link"></i> <?php echo $this->e($store['name']); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-user"></i> <?php echo $this->e($_user['name']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <li>
                <a href="<?php echo $this->url("account/$_uid"); ?>"><?php echo $this->text('Account'); ?></a>
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
  <?php if (!empty($_breadcrumbs) || !empty($_page_title)) { ?>
  <div class="container-fluid header">
  <?php if (!empty($_breadcrumbs)) { ?>
  <div class="breadcrumbs hidden-print">
    <div class="row">
      <div class="col-md-12">
        <ol class="breadcrumb">
          <?php if(!empty($_breadcrumbs)) { ?>
          <?php foreach ($_breadcrumbs as $item) { ?>
          <?php if(empty($item['url'])) { ?>
          <li><?php echo $this->filter($item['text']); ?></li>
          <?php } else { ?>
          <li><a href="<?php echo $this->e($item['url']); ?>"><?php echo $this->filter($item['text']); ?></a></li>
          <?php } ?>
          <?php } ?>
          <?php } ?>
        </ol>
      </div>
    </div>
  </div>
  <?php } ?>
  <?php if(!empty($_page_title)) { ?>
  <div class="page-title">
    <h1 class="h3"><?php echo $this->filter($_page_title); ?></h1>
  </div>
  <?php } ?>
  </div>
  <?php } ?>
  <?php if(!empty($_help)) { ?>
  <div class="container-fluid help"><?php echo $this->filter($_help); ?></div>
  <?php } ?>
  <div class="container-fluid content">
    <?php if (!empty($_messages)) { ?>
    <div class="row hidden-print" id="message">
      <div class="col-md-12">
        <?php foreach ($_messages as $type => $strings) { ?>
        <div class="alert alert-<?php echo $type; ?> alert-dismissible fade in" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span>×</span>
          </button>
          <?php foreach ($strings as $string) { ?>
          <?php echo $this->filter($string); ?><br>
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