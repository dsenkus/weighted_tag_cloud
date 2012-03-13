<?php
class WeightedTagCloudWorker {
  protected $tag_cloud;
  protected $writer;
  protected $dbh;

  /**
   * @param WeightedTagCloud $tag_cloud Tag cloud instance
   * @param WeightedTagCloudXMLWriter $writer XML Writer
   * @param array $db_settings Database connection settings
   */
  function __construct($tag_cloud, $writer, $db_settings){
    $this->tag_cloud = $tag_cloud;
    $this->writer = $writer;
    $this->db_settings = $db_settings;

    $this->connectDB();
  }

  function __destruct(){
    $this->disconnectDB();
  }

  /**
   * Perform main script actions
   */
  function run(){
    $text_array = $this->getTextFromDB();
    foreach($text_array as $text){
      $this->tag_cloud->processText($text);
    }
    $tag_array = $this->tag_cloud->getTags();
    $this->writer->setTagArray($tag_array); 
    file_put_contents('cloud_data.xml', $this->writer->generate());
  }

  /**
   * Load text from database column
   * @return array
   */
  protected function getTextFromDB(){
    $sql = "SELECT {$this->db_settings['column']} "
         . "FROM {$this->db_settings['table']}";
    $result = $this->dbh->query($sql);
    $text = array();

    while($tmp = $result->fetch_array(MYSQLI_NUM)){
      $text[] = $tmp[0];
    }

    return $text;
  }

  /**
   * Connect to database
   */
  protected function connectDB(){
    $c = $this->db_settings;

    $this->dbh = new mysqli($c['host'],$c['user'],$c['pass'],$c['db']);

    if (mysqli_connect_errno()) { 
       Utils::log(sprintf("Could not connect to the DB: %s\n", mysqli_connect_error())); 
       exit(); 
    } 
  }

  /**
   * Disconnect from database
   */
  protected function disconnectDB(){
    if($this->dbh){
      $this->dbh->close();
    } 
  }

  protected function writeToFile($filename, $contents){
    file_put_contents($filename, $contents);
  }
}

