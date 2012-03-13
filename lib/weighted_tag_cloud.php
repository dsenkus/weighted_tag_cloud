<?php
class WeightedTagCloud {
  protected $tags;
  protected $search_tags;
  protected $total_tags;
  protected $excluded_words;
  protected $organic;
  protected $search_terms;

  /**
   * @param int $total_tags Total tags to generate
   * @param array $excluded_words List of words to exclude from tags 
   * @param bool $organic Organic/Nonorganic list
   */
  function __construct($total_tags, $excluded_words=array(), $organic=true){
    $this->total_tags = $total_tags;
    $this->excluded_words = $excluded_words;
    $this->organic = $organic;
    $this->tags = array();
  }

  /**
   * Process text to find all tags in it
   * @param string $text Text to process
   */
  function processText($text){
    // split text into words
    $temp_array = $this->splitTextIntoWords($text);

    // normalize each word
    $temp_array = array_map(array($this,'normalizeWord'), $temp_array); 

    // remove duplicates
    $temp_array = array_unique($temp_array);

    // process popular word tags
    foreach ($temp_array as $word){
      if ($this->isValidWord($word)){
        $this->addTag($word);
      }
    }

    // process search tags
    if(!empty($this->search_terms)){
      foreach($this->search_terms as $term){
        if(preg_match("/\b$term\b/i", $text)){
          $this->addSearchTag($term);
        }
      }
    }
  }   

  /**
   * Set $this->search_terms
   * @param array $terms Search terms
   */
  function setSearchTerms($terms){
    $this->search_terms = $terms;
  }

  /**
   * Return processed tags array
   * @return array
   */
  function getTags(){
    if(!empty($this->tags)){
      if($this->organic){
        // organic tags cloud
        if(!empty($this->search_tags)){
          $this->appendSearchTagsOrganic();
        }
        $this->sortTags();
        $this->sliceTagsArray();
        $this->addWeights();
      } else {
        // non organic tags cloud
        $this->sortTags();
        $this->sliceTagsArray();
        if(!empty($this->search_tags)){
          $this->appendSearchTagsUnorganic();
          $this->sortTags();
        }
        $this->addWeights();
      }
    }
    return $this->tags;
  }

  /**
   * Find and remove last non search term tag from main array
   */
  protected function removeLastNonSearchTag(){
    end($this->tags); //set the pointer to the end of the array

    while(true) {
      $lastKey = key($this->tags);

      if (!in_array($lastKey, $this->search_terms)){
        unset($this->tags[$lastKey]);
        break;
      } else {
        if(!prev($this->tags)){
          break;
        }
      }
    }
  }

  /**
   * Append all search terms to main array, in a non organic way
   * Used to append low ranking search terms to main array
   */
  protected function appendSearchTagsUnorganic(){
    foreach($this->search_tags as $k => $v){
      if(!isset($this->tags[$k])){
        $this->removeLastNonSearchTag();
      }
    }

    // add search tags
    foreach($this->search_tags as $k => $v){
      if(!isset($this->tags[$k])){
        $this->tags[$k] = $v;
      }
    }
  }

  /**
   * Append all search terms to main array, in organic way
   */
  protected function appendSearchTagsOrganic(){
    foreach($this->search_tags as $k => $v){
      if(!empty($this->tags[$k])){
        $this->tags[$k]['count'] += $v['count'];
      } else {
        $this->tags[$k] = $v;
      }
    }
  }

  /**
   * Return normalized word
   * @param string $word Word
   * @return string
   */
  protected function normalizeWord($word){
    $result = strtolower($word);
    return $result;
  }

  /**
   * Return tags range
   * @return int
   */ 
  protected function getTagsRange(){
    $count = count($this->tags);
    return $count <= $this->total_tags ? $count : $this->total_tags;
  }

  /**
   * Sort main tags array by occurence count
   */
  protected function sortTags(){
    uasort($this->tags, array($this,'sortCmp'));
  }

  /**
   * Function to use in uasort
   */
  protected function sortCmp($a, $b){
    if ($a['count'] == $b['count']) {
        return 0;
    }
    return ($a['count'] > $b['count']) ? -1 : 1;
  }

  /**
   * Slice array to remove out of range tags
   */
  protected function sliceTagsArray(){
    $this->tags = array_slice($this->tags, 0, $this->getTagsRange(),true);
  }

  /**
   * Calculate and add weights to all tags
   */
  protected function addWeights(){
    $keys = array_keys($this->tags);

    $max = $this->tags[$keys[0]]['count'];  
    $min = $this->tags[$keys[count($keys)-1]]['count'];  
    $delta = $max - $min;
    $delta = $delta == 0 ? 1 : $delta;

    for($i=0; $i<count($keys); $i++){
      // weight = round((count - min) * 100 / delta)
      $this->tags[$keys[$i]]['weight'] = 
        round(($this->tags[$keys[$i]]['count']-$min)*100 / $delta); 
    }
  }

  /**
   * Check if word is valid
   * @param string $word Word
   * @return bool
   */
  protected function isValidWord($word){
    $valid = !in_array($word, $this->excluded_words)
      && strlen($word) >= 3;
    return $valid; 
  }

  /**
   * Split text into words
   * @param string @text Text to work with
   * @result array
   */
  protected function splitTextIntoWords($text){
    $result = preg_split(
      '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', 
      $text, -1, PREG_SPLIT_NO_EMPTY
    );

    return $result;
  }

  /**
   * Add tag to main array
   * @param string $tag Tag to add
   */
  protected function addTag($tag){
    if(empty($this->tags[$tag])){
      $this->tags[$tag] = array(
        'count' => 1,
      );
    } else {
      $this->tags[$tag]['count']++;
    }
  }

  /**
   * Add search tag to search tags array
   * @param string $tag Tag to add
   */
  protected function addSearchTag($tag){
    if(empty($this->search_tags[$tag])){
      $this->search_tags[$tag] = array(
        'count' => 1,
      );
    } else {
      $this->search_tags[$tag]['count']++;
    }
  }
}
