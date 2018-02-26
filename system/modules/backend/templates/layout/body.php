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
        <span class="navbar-brand"><?php echo $this->text('GPLCart'); ?></span>
        <?php } else { ?>
        <a class="navbar-brand" href="<?php echo $this->url('admin'); ?>" title="<?php echo $this->text('Dashboard'); ?>">
        <?php echo $this->text('GPLCart'); ?>
        </a>
        <?php } ?>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <?php if(!empty($_menu)) { ?>
        <?php echo $_menu; ?>
        <?php } ?>
        <ul class="nav navbar-nav navbar-right right-links hidden-sm hidden-xs">
          <li>
            <a href="<?php echo $this->url('admin/help'); ?>" title="<?php echo $this->text('Help'); ?>">
              <i class="fa fa-info-circle"></i>
            </a>
          </li>
          <?php if($this->access('bookmark')) { ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle " data-toggle="dropdown" title="<?php echo $this->text('Bookmarks'); ?>">
              <i class="fa fa-star"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <?php if(!empty($_bookmarks)) { ?>
              <?php foreach ($_bookmarks as $bookmark) { ?>
              <li>
                <a href="<?php echo $this->url($bookmark['path']); ?>">
                  <?php if(empty($bookmark['title'])) { ?>
                  <?php echo $this->e($this->truncate($bookmark['path'], 50)); ?>
                  <?php } else { ?>
                  <?php echo $this->e($this->truncate($bookmark['title'], 50)); ?>
                  <?php } ?>
                </a>
              </li>
              <?php } ?>
              <li class="divider"></li>
              <li>
                <a href="<?php echo $this->url('admin/bookmark'); ?>"><?php echo $this->text('See all'); ?></a>
              </li>
              <?php } ?>
              <?php if(empty($_is_bookmarked)) { ?>
              <?php if($this->access('bookmark_add')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/bookmark/add', array('title' => $_head_title, 'path' => $_path)); ?>"><?php echo $this->text('Add'); ?></a>
              </li>
              <?php } ?>
              <?php } else { ?>
              <?php if($this->access('bookmark_delete')) { ?>
              <li>
                <a href="<?php echo $this->url('admin/bookmark/delete', array('path' => $_path)); ?>"><?php echo $this->text('Delete'); ?></a>
              </li>
              <?php } ?>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?php echo $this->text('Account'); ?>">
              <i class="fa fa-user"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <li>
                <a href="<?php echo $this->url("account/$_uid"); ?>"><?php echo $this->text('Account'); ?></a>
              </li>
              <li>
                <a href="<?php echo $this->url('logout'); ?>"><?php echo $this->text('Log out'); ?></a>
              </li>
            </ul>
          </li>
          <?php if(count($_language_switcher) > 1) { ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <?php if (empty($_language_switcher[$_langcode])) { ?>
              <?php echo $this->text('Select language'); ?>
              <?php } else { ?>
              <?php echo $this->e($_language_switcher[$_langcode]['native_name']); ?>
              <?php } ?>
            </a>
            <ul class="dropdown-menu">
              <?php foreach ($_language_switcher as $language) { ?>
              <?php if ($language['code'] !== $_langcode) { ?>
              <li>
                <a href="<?php echo $this->lurl($language['code'], '', $_query); ?>"><?php echo $this->e($language['native_name']); ?></a>
              </li>
              <?php } ?>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </nav>
  <?php if (!empty($_breadcrumbs) || !empty($_page_title)) { ?>
  <nav class="navbar navbar-default navbar-static-top hidden-print">
    <div class="container-fluid header">
      <?php if (!empty($_breadcrumbs)) { ?>
      <div class="breadcrumbs hidden-print">
        <div class="row">
          <div class="col-md-12">
            <ol class="breadcrumb">
              <?php if(!empty($_breadcrumbs)) { ?>
              <?php foreach ($_breadcrumbs as $item) { ?>
              <?php if(empty($item['url'])) { ?>
              <li><?php echo $item['text']; ?></li>
              <?php } else { ?>
              <li><a href="<?php echo $this->e($item['url']); ?>"><?php echo $item['text']; ?></a></li>
              <?php } ?>
              <?php } ?>
              <?php } ?>
            </ol>
          </div>
        </div>
      </div>
      <?php } ?>
      <?php if(!empty($_page_title)) { ?>
      <h1 class="h3">
        <?php echo $_page_title; ?>
        <?php if(!empty($_help['path'])) { ?>
          <a class="small" href="<?php echo $this->url($_help['path']); ?>"><i class="fa fa-question-circle"></i></a>
        <?php } ?>
      </h1>
      <?php } ?>
    </div>
  </nav>
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