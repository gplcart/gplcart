<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
  <head>
    <?php foreach($meta as $tag) { ?>
    <meta<?php echo $this->attributes($tag); ?>>
    <?php } ?>
	<title><?php echo $head_title; ?></title>
    <?php foreach($css as $href => $data) { ?>
    <link href="<?php echo $href; ?>" rel="stylesheet">
    <?php } ?>
    <?php foreach($js_top as $src => $data) { ?>
    <script src="<?php echo $src; ?>"></script>
    <?php } ?>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>