<?php
header('Content-Type: text/js');
require_once("../panels.php");
if (isset($panels)){
  $buffer = '';
  foreach($panels as $p){
    $file = file_get_contents("{$p}.js");
    $file = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $file);
    $file = preg_replace('!//.*?\n!', '', $file);
    //$file = str_replace(': ', ':', $file);
    $file = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $file);
    $buffer .= $file;
  }

  // Remove comments
  //$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
 
  // Remove space after colons
  //$buffer = str_replace(': ', ':', $buffer);
 
  // Remove whitespace
  //$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
 
  // Enable GZip encoding.
  //ob_start("ob_gzhandler");
  echo $buffer;
}
?>