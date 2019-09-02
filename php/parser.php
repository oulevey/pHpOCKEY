<?php
// define the matrix size
define('MATRIX_COLS', 30);
define('MATRIX_ROWS', 60);
define('XPATH_BASE', "/GAMEEXPORT/GAMEACTIONS/GAMEACTION[TEAM='Genève-Servette HC'][DETAIL='SOG']");

// create an empty matrix filled with 0 value
function createEmptyMatrix($cols, $rows) {
  return array_fill(0, $rows, array_fill(0, $cols, 0));
}

// parse an XML file and return the matrix,
// optionaly pass a old matrix to merge with.
function parseFile($file, $matrix = []) {
  if (empty($matrix)) {
    // create an empty matrix filled with 0 value
    $matrix = createEmptyMatrix(MATRIX_COLS, MATRIX_ROWS);
  }

	// load the XML file
  $xml = simplexml_load_file($file);

  // extract the coords
	$x_nodes = $xml->xpath(XPATH_BASE . "/POSITION_X");
	$y_nodes = $xml->xpath(XPATH_BASE . "/POSITION_Y");

  // for each x coord
  foreach ($x_nodes as $key => $node) {
    // round the value to get an integer
    $x = round((string) $node);
    $y = round((string) $y_nodes[$key]);

    // update the matrix
    $matrix[$y][$x]++;
  }

  return $matrix;
}

// list and parse all XML files in the provided directory (not recursive)
function parseDirectory($directory) {
  // get all files in the directory, skipping the dots alias
  $files = array_diff(scandir($directory), array('..', '.'));

  // set the global matrix
  $matrix = [];

  // for each file in the Directory
  foreach ($files as $file) {
    // get the file extension (force lower case)
    $info = pathinfo($file);
    $ext = strtolower($info['extension']);

    // skip non XML file
    if ($ext !== 'xml') {
      continue;
    }

    // parse the file and pass the global matrix with
    $matrix = parseFile("{$directory}/{$file}", $matrix);
  }

  return $matrix;
}

// convert a matrix to heatmap file
function toHeatMap($path, $matrix) {
  $data = [];
  foreach ($matrix as $y => $row) {
    array_push($data, implode(" ", $row));
  }
  file_put_contents($path, implode("\n", $data));
}

// convert a matrix to OpenScad file
function toOpenSCAD($path, $matrix) {
  $data = ["module heatmap(sr = 1, sh = 1){"];
  foreach ($matrix as $y => $row) {
    foreach ($row as $x => $value) {
      if ($value <= 0) continue;
      array_push($data, "  translate([{$x}, {$y}, 0]) cylinder(r = {$value} * sr, h = {$value} * sh);");
    }
  }
  array_push($data, "}");
  file_put_contents($path, implode("\n", $data));
}

// =============================================================================
// EXPERIMENTAL
// =============================================================================

require_once(ROOT_PATH . "/php/toPNG.php");
