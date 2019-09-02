<?php
function printVersion() {
  echo(APP_VERSION . "\n");
}

function printBanner() {
  echo(APP_NAME . " v" . APP_VERSION . "\n");
}

function printUsage() {
  echo("\nUsage:\n");
  echo("  php index.php <input> <output> [...option]\n\n");
  echo("Arguments:\n");
  echo("  input   Directory with some GAMEEXPORT XML files.\n");
  echo("  output  Directory where the heatmaps will be saved.\n\n");
  echo("Options:\n");
  echo("  --png      Build a PNG heatmap.\n");
  echo("  --scad     Build a OpenSCAD heatmap.\n");
  echo("  --help     Show this message and exit. \n\n");
  echo("  --version  Print application version. \n\n");
}

// print error, help and exit
function printError($message) {
  echo("Error: {$message}\n");
  printUsage();
  exit(1);
}
