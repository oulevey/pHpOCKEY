<?php
// application name and version
define('APP_NAME', "\npHpOCKEY");
define('APP_VERSION', "0.4.2");

// Absolute path to root directory
define('ROOT_PATH', dirname(__FILE__));

// import functions
require_once(ROOT_PATH . "/php/helpers.php");
require_once(ROOT_PATH . "/php/parser.php");

// print help
if (in_array("--help", $argv)) {
  printBanner();
  printUsage();
  exit(1);
}

// print version
if (in_array("--version", $argv)) {
  printVersion();
  exit(1);
}

// not enough arguments
if ($argc < 3) {
  printError("not enough arguments");
  exit(1);
}

// get user input
$args   = $argv;
$file   = array_shift($args);
$input  = array_shift($args);
$output = array_shift($args);

// current working directory (from where this file is called)
$cwd = dirname(realpath($file));

// sanitize user input
if (!is_dir($input)) {
  printError("<input> directory not found: {$input}");
  exit(1);
}

if (!is_dir($output)) {
  // try to make the directory (@silent error)
  if (!@mkdir($output)) {
    printError("<output> directory not found: {$output}");
    exit(1);
  }
}

// get absolute path
$input  = realpath($input);
$output = realpath($output);

// parse all file from input directory
$matrix = parseDirectory($input);

// print banner
printBanner();

// create and save the basic text based heatmap
$heatmap = "{$output}/heatmap.dat";
toHeatMap($heatmap, $matrix);
echo("\nCreate: {$heatmap}\n");

// create and save OpenSCAD heatmap
if (in_array("--scad", $argv)) {
  $heatmap = "{$output}/heatmap.scad";
  toOpenSCAD($heatmap, $matrix);
  copy(ROOT_PATH . "/scad/build.scad", "{$output}/build.scad");
  copy(ROOT_PATH . "/scad/config.scad", "{$output}/config.scad");
  echo("Create: {$heatmap}\n");
}

// create and save PNG heatmap
if (in_array("--png", $argv)) {
  $heatmap = "{$output}/heatmap.png";
  toPNG($heatmap, $matrix);
  echo("Create: {$heatmap}\n");
}

// print done message
exit("\nDone!\n");
