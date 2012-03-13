<?php
require_once dirname(__FILE__) . '/../lib/utils.php';
require_once dirname(__FILE__) . '/../lib/weighted_tag_cloud.php';

function test($a1, $a2, $n){
  if($a1 != $a2){
    echo "#{$n} Test fail! Result Array:\n";
    var_dump($a2);
  } else {
    echo "#{$n} Test passed\n";
  }
}

/*********************************************
 * Basic scenario
 ********************************************/
$expected = array(
  'aaa' => array('count' => 1, 'weight' => 0),
  'bbbb' => array('count' => 1, 'weight' => 0),
  'ccc' => array('count' => 1, 'weight' => 0),
);

$exclude = array();
$search_terms = array();
$wtc = new WeightedTagCloud(4,$exclude,true);
$wtc->setSearchTerms($search_terms);
$wtc->processText("aaa bbbb d ccc");
$result = $wtc->getTags();

test($expected,$result,1);

/*********************************************
 * #1 Exclude single word
 ********************************************/
$expected = array(
  'bbbb' => array('count' => 1, 'weight' => 0),
  'ccc' => array('count' => 1, 'weight' => 0),
);

$exclude = array('aaa');
$search_terms = array();
$wtc = new WeightedTagCloud(3,$exclude,true);
$wtc->setSearchTerms($search_terms);
$wtc->processText("aaa bbbb ccc");
$result = $wtc->getTags();

test($expected,$result,2);

/*********************************************
 * #2 Exclude multiple word
 ********************************************/
$expected = array(
  'ccc' => array('count' => 1, 'weight' => 0),
);

$exclude = array('aaa','bbbb');
$search_terms = array();
$wtc = new WeightedTagCloud(3,$exclude,true);
$wtc->setSearchTerms($search_terms);
$wtc->processText("aaa bbbb ccc");
$result = $wtc->getTags();

test($expected,$result,3);

/*********************************************
 * #3 With Search term
 ********************************************/
$expected = array(
  'ccc' => array('count' => 1, 'weight' => 0),
  'bbbb ccc' => array('count' => 1, 'weight' => 0),
);

$exclude = array('aaa','bbbb');
$search_terms = array('bbbb ccc');
$wtc = new WeightedTagCloud(3,$exclude,true);
$wtc->setSearchTerms($search_terms);
$wtc->processText("aaa bbbb ccc");
$result = $wtc->getTags();

test($expected,$result,4);

/*********************************************
 * #5 Total terms limit, excluding, searching
 ********************************************/
$expected = array(
  'bbbb ccc' => array('count' => 2, 'weight' => 100),
  'ccc' => array('count' => 2, 'weight' => 100),
  'eee' => array('count' => 1, 'weight' => 0),
);

$exclude = array('bbbb');
$search_terms = array('bbbb ccc');
$wtc = new WeightedTagCloud(3,$exclude,true);
$wtc->setSearchTerms($search_terms);
$wtc->processText("aaa bbbb ccc ddd eee");
$wtc->processText("bbbb ccc");
$result = $wtc->getTags();

test($expected,$result,5);

/*********************************************
 * #6 Total terms limit, excluding, searching
 ********************************************/
$expected = array(
  'bbbb ccc' => array('count' => 1, 'weight' => 0),
  'eee' => array('count' => 3, 'weight' => 100),
  'aaa' => array('count' => 3, 'weight' => 100),
);

$exclude = array('bbbb');
$search_terms = array('bbbb ccc');
$wtc = new WeightedTagCloud(3,$exclude,false);
$wtc->setSearchTerms($search_terms);
$wtc->processText("aaa bbbb ccc ddd eee");
$wtc->processText("ccc");
$wtc->processText("eee aaa");
$wtc->processText("eee aaa");
$result = $wtc->getTags();

test($expected,$result,6);

/*********************************************
 * #7 Weights
 ********************************************/
$expected = array(
  'bbbb ccc' => array('count' => 1, 'weight' => 0),
  'ccc' => array('count' => 2, 'weight' => 50),
  'eee' => array('count' => 3, 'weight' => 100),
  'aaa' => array('count' => 3, 'weight' => 100),
);

$exclude = array('bbbb');
$search_terms = array('bbbb ccc');
$wtc = new WeightedTagCloud(5,$exclude,false);
$wtc->setSearchTerms($search_terms);
$wtc->processText("aaa bbbb ccc ddd eee");
$wtc->processText("ccc");
$wtc->processText("eee aaa");
$wtc->processText("eee aaa");
$result = $wtc->getTags();

test($expected,$result,7);

/*********************************************
 * #8 Search for nonexisting terms should not
 * add it to result array
 ********************************************/
$expected = array(
  'bbbb ccc' => array('count' => 1, 'weight' => 0),
  'ccc' => array('count' => 2, 'weight' => 50),
  'eee' => array('count' => 3, 'weight' => 100),
  'aaa' => array('count' => 3, 'weight' => 100),
);

$exclude = array('bbbb');
$search_terms = array('bbbb ccc', 'abc');
$wtc = new WeightedTagCloud(5,$exclude,false);
$wtc->setSearchTerms($search_terms);
$wtc->processText("aaa bbbb ccc ddd eee");
$wtc->processText("ccc");
$wtc->processText("eee aaa");
$wtc->processText("eee aaa");
$result = $wtc->getTags();

test($expected,$result,8);

/*********************************************
 * #9 Should work with empty array 
 ********************************************/
$expected = array();

$exclude = array();
$search_terms = array();
$wtc = new WeightedTagCloud(5,$exclude,false);
$wtc->setSearchTerms($search_terms);
$wtc->processText("");
$result = $wtc->getTags();

test($expected,$result,9);
