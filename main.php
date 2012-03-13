<?php
require_once dirname(__FILE__) . '/lib/utils.php';
require_once dirname(__FILE__) . '/lib/weighted_tag_cloud.php';
require_once dirname(__FILE__) . '/lib/weighted_tag_cloud_xml_writer.php';
require_once dirname(__FILE__) . '/lib/weighted_tag_cloud_worker.php';

/**
 * Configuration START
 */
$exclude_words = array('the','for','and','from','with','just',
'why','that','like','was','want','have','day','right',
'you','big');

$search_terms = array('donuts','just saw a', 'island');

$total_terms = 20;

// Nonorganic tag cloud will have search terms appended even
// if their rank is too low to be shown in $total_terms.
//
// Organic tag cloud will be ordered entirely by tag ranks.
$organic = false;

// Fake weighting will set font size according to position of
// the tag, instead of actual weight.
$fake_weighting = true;

$small_font = 10;
$large_font = 40;

$hicolor = "0xffffff";

$database = array(
  'host'   => 'localhost',
  'user'   => 'root',
  'pass'   => '',
  'db'     => 'hashtags',
  'table'  => 'tweets',
  'column' => 'caption',
);


$wtc = new WeightedTagCloud($total_terms,$exclude_words,$organic);
$xml_writer = new WeightedTagCloudXMLWriter($small_font,$large_font,$hicolor,
  $fake_weighting);

$wtc->setSearchTerms($search_terms);
$worker = new WeightedTagCloudWorker($wtc, $xml_writer, $database);

$worker->run();
