<?php
class WeightedTagCloudXMLWriter {
  protected $tag_array;
  protected $tag_count;
  protected $small_font;
  protected $large_font;
  protected $fake_weighting;
  protected $hicolor;

  /**
   * @param int $small_font Smallest font value
   * @param int $large_font Largest font value
   * @param string $hicolor Hex color value
   */
  function __construct($small_font, $large_font, $hicolor, $fake_weighting){
    $this->small_font = $small_font;
    $this->large_font = $large_font;
    $this->hicolor = $hicolor;
    $this->fake_weighting = $fake_weighting;
  }

  /**
   * Set tags array
   * @param array $tags Processed WeightedTagsCloud result
   */
  function setTagArray($tags){
    $this->tag_array = $tags;
    $this->tag_count = count($tags);
  }

  /**
   * Generate XML document
   * @return string
   */
  function generate(){
    if(!empty($this->tag_array)){
      $doc = new DomDocument('1.0');
      $tags = $doc->createElement('tags');
      $tags = $doc->appendChild($tags);

      foreach ($this->tag_array as $k=>$v){
        $a = $doc->createElement('a');
        $a = $tags->appendChild($a);

        // set attributes
        $a->setAttribute('href', 'index.php?key='.urlencode($k));
        $a->setAttribute('hicolor', $this->hicolor);
        $a->setAttribute('style', 'font-size: ' 
          . $this->getFontSize($k) . 'pt;');

        $tag = $doc->createTextNode($k);
        $tag = $a->appendChild($tag);
      }

      return $doc->saveXML();
    }
  }

  /**
   * Calculate item font size
   * @param int $key Item array key
   * @return int
   */
  protected function getFontSize($key){
    if($this->fake_weighting){
      $font = $this->getFontSizeByPos(Utils::array_pos($key, $this->tag_array));
    } else {
      $font = $this->getFontSizeByWeight($this->tag_array[$key]['weight']);
    }
    Utils::log($font);
    return $font;
  }

  /**
   * Calculate font size for a given weight
   * @param int $weight Weight
   * @return int
   */
  protected function getFontSizeByWeight($weight){
    $delta = $this->large_font - $this->small_font;
    $font = ceil($this->small_font + $delta * $weight / 100);
    return $font;
  }

  /**
   * Calculate font size for a given position
   * @param int $pos Position
   * @return int
   */
  protected function getFontSizeByPos($pos){
    $delta = $this->large_font - $this->small_font;
    $font = $this->large_font - round(($delta / ($this->tag_count)) * $pos);

    return $font; 
  }
}
