<?php
/**
 * Black Sets Theme Header.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class( bsets_body_class() ); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#content">Skip to content</a>
<header class="site-header" id="header">
  <div class="header-inner">
    <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="Black Sets Home">
      <img src="<?php echo esc_url( get_theme_file_uri( 'assets/bsets-logo.png' ) ); ?>" alt="Black Sets" class="brand-logo" width="160" height="48">
    </a>
    <button class="menu-toggle" id="menuToggle" aria-expanded="false" aria-controls="primaryNav" aria-label="Toggle navigation menu">
      <span class="menu-label">Menu</span>
      <span class="bars" aria-hidden="true"></span>
    </button>
    <nav class="nav" id="primaryNav" aria-label="Primary navigation">
      <?php bsets_render_menu(); ?>
    </nav>
  </div>
</header>
<main id="content" class="site-main">

