<?php
/**
 * Loads the framework and the theme.
 * 
 * @package Plus Framework
 */

/* Skip Loading Everything */
  if ( $_SERVER['REQUEST_URI'] === '/favicon.ico' ) return FALSE;
  
/* Named Constants */
  define('FRAMEWORK', get_template_directory());
  define('FRES', get_template_directory_uri());
  define('THEME', get_stylesheet_directory());
  define('BLOCK', get_stylesheet_directory().'/blocks');
  define('BLK', get_stylesheet_directory_uri().'/blocks');
  define('RESOURCE', get_stylesheet_directory().'/resources');
  define('RES', get_stylesheet_directory_uri().'/resources');
  define('PARTIAL', get_stylesheet_directory().'/templates/partials/');

/* Require Classes */
  require_once('includes/class.fns.php');
  require_once('includes/class.inflect.php');
  require_once('includes/class.init.php');
  
  if ( file_exists(THEME . '/theme.php') ) require_once(THEME . '/theme.php');
  
/* Initializers */
  $frwk       = new plus_init;
  $frwk_theme = class_exists('plus_theme') ? new plus_theme() : FALSE;