<?php
header('Content-Type: text/css');
require_once("../panels.php");
if (isset($panels)){
  $buffer = '';
  foreach($panels as $p){
    $buffer .= file_get_contents("{$p}.css");
  }

  // Remove comments
  $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
 
  // Remove space after colons
  $buffer = str_replace(': ', ':', $buffer);
 
  // Remove whitespace
  $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
 
  // Enable GZip encoding.
  //ob_start("ob_gzhandler");
  echo $buffer;
}
?>