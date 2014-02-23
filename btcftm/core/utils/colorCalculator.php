<?php
class ColorCalculator
{
  public $color;

  public function __construct($color=NULL)
  {
    $this->setColor($color);
  }

  public function setColor($color)
  {
    $this->color = $color;
  }

  public function lightenColor($perc)
  {
    return $this->_adjustColor(-$perc);
  }

  public function darkenColor($perc)
  {
    return $this->_adjustColor($perc);
  }

  private function _adjustColor($perc = 0) 
  {
    if (!$this->color) { return ""; }

    $perc = round($perc/100,2);
    $c = $this->color;

    if(is_array($c)) {
      $r = $c["r"] - (round($c["r"])*$perc);
      $g = $c["g"] - (round($c["g"])*$perc);
      $b = $c["b"] - (round($c["b"])*$perc);

      return array(
        "r"=> round(max(0,min(255,$r))),
        "g"=> round(max(0,min(255,$g))),
        "b"=> round(max(0,min(255,$b)))
      );
    } else if(preg_match("/#/",$c)) {
      $hex = str_replace("#","",$c);
      $r = (strlen($hex) == 3)? hexdec(substr($hex,0,1).substr($hex,0,1)):hexdec(substr($hex,0,2));
      $g = (strlen($hex) == 3)? hexdec(substr($hex,1,1).substr($hex,1,1)):hexdec(substr($hex,2,2));
      $b = (strlen($hex) == 3)? hexdec(substr($hex,2,1).substr($hex,2,1)):hexdec(substr($hex,4,2));
      $r = round($r - ($r*$perc));
      $g = round($g - ($g*$perc));
      $b = round($b - ($b*$perc));

      return "#".str_pad(dechex( max(0,min(255,$r)) ),2,"0",STR_PAD_LEFT)
          .str_pad(dechex( max(0,min(255,$g)) ),2,"0",STR_PAD_LEFT)
          .str_pad(dechex( max(0,min(255,$b)) ),2,"0",STR_PAD_LEFT);
 
    }
  }
}

?>