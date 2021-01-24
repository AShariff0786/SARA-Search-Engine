<?php

$userLink = $_POST['q'];
try {
    $pdo = new PDO('mysql:host = 149.4.211.180; dbname=shas2679', 'shas2679', 'CS355');
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
}
catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
}

$already_crawled = array();
$crawlarray;
$already_added_word=array();


function get_text($url){
  $options = array('http' => array('method' => "GET", 'headers' => "User-Agent: SARABot\n"));

  $context = stream_context_create($options);

  $doc = new DOMDocument();
  @$doc-> loadHTML(@file_get_contents($url, false, $context));

  $list = $doc -> getElementsByTagName("p");
  $string = "";
  $length= $list -> length;
  echo "LENGTH: ".$length;
  for($i =0; $i < $length; $i++){
    $list = $doc -> getElementsByTagName("p");
    $list = $list -> item($i) -> nodeValue;
    $list = str_ireplace(",","",$list);
    $list = str_ireplace(".","",$list);
    $list = str_ireplace("!","",$list);
    if($list != ' ' || $list !=''){
      $string .= $list;
    }
  }
  return $string;
}

function get_details($url){

  $options = array('http' => array('method' => "GET", 'headers' => "User-Agent: SARABot\n"));

  $context = stream_context_create($options);

  $doc = new DOMDocument();
  @$doc-> loadHTML(@file_get_contents($url, false, $context));

  $title = $doc -> getElementsByTagName("title");
  $title = $title -> item(0) -> nodeValue;

  $description="";
  $keywords="";
  $metas = $doc -> getElementsByTagName("meta");
  $headers = get_headers($url, 1);
  $lastModified = $headers['Last-Modified'];
  $modified= "";
  if($lastModified != ""){
    $lastModified= str_ireplace(" ", "-", $lastModified);
    $lastModified = explode("-",$lastModified);
    $modified .= $lastModified[3]."-";
    $nmonth = date("m", strtotime($lastModified[2]));
    $modified .= $nmonth."-";
    $modified .= $lastModified[1];
  }
  for($i =0; $i < $metas -> length; $i++){
    $meta = $metas -> item($i);

    if(strtolower($meta ->getAttribute("name")) == strtolower("description") ){
      $description = $meta -> getAttribute("content");
    }
    if(strtolower($meta ->getAttribute("name") )== strtolower("keywords") ){
      $keywords = $meta -> getAttribute("content");
    }
  }

  return '{ "Title": "'.str_replace("\n", "", $title).'", "Description": "'.str_replace("\n", "", $description).'", "Keywords": "'.str_replace("\n", "", $keywords).'", "URL": "'.$url.'", "LastModified" : "'.$modified.'"}';

}

function follow_links($url, $depth){
  if($depth <= 0){
    $pdo -> close();
    echo "<script>window.location = 'https://venus.cs.qc.cuny.edu/~shas2679/admin.html'</script>";
  }
  global $already_crawled;
  global $crawlarray;
  global $pdo;
  global $already_added_word;

  $currentTime = time();

  $options = array('http' => array('method' => "GET", 'headers' => "User-Agent: SARABot\n"));

  $context = stream_context_create($options);

  $doc = new DOMDocument();
  @$doc-> loadHTML(@file_get_contents($url, false, $context));
  $linklist = $doc->getElementsByTagName("a");
  echo"<br>URL:".$url."<br>";
  foreach ($linklist as $link) {
    $l = $link ->getAttribute("href");

    echo "<br>Link: ".$l."<br>END<br>";

    echo $l;
    if(substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//"){
      $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
    }
    else if(substr($l, 0, 2) == "//"){
      $l = parse_url($url)["scheme"].":".$l;
    }
    else if(substr($l, 0, 2) == "./"){
      $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
    }
    else if(substr($l, 0, 1) == "#"){
      $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
    }
    else if(substr($l, 0, 3) == "../"){
      $l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
    }
    else if(substr($l, 0, 11) == "javascript:"){
      continue;
    }
    else if(substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http"){
      $l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
    }

    print_r($already_crawled);
    if(!in_array($l, $already_crawled)){
        $already_crawled[] = $l;
        $crawlarray[] = $l;

        $details = json_decode(get_details($l));


        echo "testA";
        $text= get_text($l);
        $words = explode(" ",$text);
        $length = sizeof($words);
        echo "LENGTH: ".$length;
        print_r($words);
        echo "test";
        try{
          $pageID = $pdo ->query("SELECT pageId FROM `page` WHERE url_hash='".md5($details->URL)."'");
          $pageID = $pageID ->fetchAll();
          $pageID = $pageID[0]['pageId'];
        }
        catch(Exception $e){
            echo "PageId failed: " . $e->getMessage();
        }
        echo "<br>PAGEID: ".$pageID."<br>";
        if($pageID != NULL){
          for($i =0; $i < $length; $i++){
            try{
              $wordID = $pdo ->query("SELECT wordId FROM `word` WHERE wordName='".$words[$i]."'");
              $wordID = $wordID ->fetchAll();
              print_r($wordID);
              $wordID = $wordID[0]['wordId'];
              echo "WORDID: ".$wordID." WORD: ".$words[$i]."END<br>";
            }
            catch(Exception $e){
              echo "WordId failed: " . $e->getMessage();
            }
            if($wordID==Null && !in_array($words[$i], $already_added_word)){
              $already_added_word[] = $words[$i];
              $wordParams = array (':wordName' => $words[$i]);
              try{
                $result = $pdo->prepare("INSERT INTO `word` (wordName) VALUES ( :wordName)");
                $result = $result->execute($wordParams);
              }
              catch(Exception $e){
                echo "Insert into word Table failed: " . $e->getMessage();
              }
            }
            if ($wordID!=NULL){
              try{
                $pagewordID = $pdo ->query("SELECT pageWordId FROM `page_word` WHERE pageId= '".$pageID."' AND wordId='".$wordID."'");
                $pagewordID = $pagewordID ->fetchAll();
                $pagewordID = $pagewordID[0]['pageWordId'];
                echo "<br>PAGEWORDID: ".$pagewordID."END<br>";
              }
              catch(Exception $e){
                echo "PageWordId failed: " . $e->getMessage();
              }
            if($pagewordID == NULL){
              try{
                $pagewordParams = array (':pageID' => $pageID, ':wordID' => $wordID);
                $result = $pdo->prepare("INSERT INTO `page_word` (pageId, wordId, freq) VALUES (:pageID, :wordID ,1)");
                $result = $result->execute($pagewordParams);
              }
              catch(Exception $e){
                echo "Insert page_word Table failed: " . $e->getMessage();
              }
            }
            else{
              try{
                $freq = $pdo ->query("SELECT freq FROM `page_word` WHERE pageId='".$pageID."' AND wordId='".$wordID."'");
                $freq = $freq ->fetchAll();
                $freq = $freq[0]['freq'] + 1;
                $pagewordParams = array (':pageID' => $pageID, ':wordID' => $wordID, ':freq' => $freq );
                $result = $pdo->prepare("UPDATE `page_word` SET pageId=:pageID, wordId=:wordID, freq= :freq WHERE pageId= :pageID AND wordId=:wordID ");
                $result = $result->execute($pagewordParams);
              }
              catch(Exception $e){
                echo "Update page_word Table failed: " . $e->getMessage();
              }
            }
          }
          }
        }

        echo "<br><br>";
        print_r($l);
        echo "<br><br>";
        $rows = $pdo ->query("SELECT * FROM `page` WHERE url_hash='".md5($details->URL)."'");
        $rows = $rows -> fetchColumn();

        echo "<br>ROWS: ".$rows. "<br>ENDROW<br>";

        $lastIndexed =date ("Y-m-d ", getlastmod());
        $accessTime = time();
        $indexTime = $accessTime - $currentTime;

        $lastModified = str_ireplace(" ","",$details->LastModified);
        $params = array (':url' => $details->URL,':title' => $details->Title, ':description' => $details->Description, ':lastModified' => $lastModified ,':lastIndexed' => $lastIndexed, ':timeToIndex' => $indexTime ,':url_hash' => md5($details->URL));

        echo "<br><br>".$lastModified."<br>End";
        print_r ($params);
        if($rows > 0){
          if(!is_null($params[':title']) && !is_null($params[':description']) && !is_null($params[':title'] != '')){
            if(!empty($params[':lastModified'])){
              try{
                $result = $pdo->prepare("UPDATE `page` SET url=:url, title=:title, description=:description, lastModified=:lastModified, lastIndexed=:lastIndexed, timeToIndex=:timeToIndex, url_hash=:url_hash WHERE url_hash=:url_hash");
                $result = $result->execute($params);
              }
              catch(PDOException $e){
                  echo "Insert into Table failed: " . $e->getMessage();
              }
            }
            else {
              try{
                $result = $pdo->prepare("UPDATE `page` SET url=:url, title=:title, description=:description,  lastIndexed=:lastIndexed, timeToIndex=:timeToIndex, url_hash=:url_hash WHERE url_hash=:url_hash");
                $result = $result->execute($params);
              }
              catch(PDOException $e){
                  echo "Update Table failed: " . $e->getMessage();
              }
            }
          }
        }
        else{
          if(!is_null($params[':title']) && !is_null($params[':description']) && !is_null($params[':title'] != '')){
            echo "Q";
            print_r($params);
            if(!empty($params[':lastModified'])){
              echo "Test1";
              $result = $pdo->prepare("INSERT INTO `page` (url, title, description, lastModified, lastIndexed, timeToIndex, url_hash) VALUES (:url, :title, :description, :lastModified, :lastIndexed, :timeToIndex, :url_hash)");
              $result = $result->execute($params);
            }
            else{
              echo "Test2";
              $params = array (':url' => $details->URL,':title' => $details->Title, ':description' => $details->Description, ':lastIndexed' => $lastIndexed, ':timeToIndex' => $indexTime ,':url_hash' => md5($details->URL));
              $result = $pdo->prepare("INSERT INTO `page` (url, title, description, lastIndexed, timeToIndex, url_hash) VALUES (:url, :title, :description, :lastIndexed, :timeToIndex, :url_hash)");
              $result = $result->execute($params);
              echo "Finish";
            }
          }
        }
    }
  }

  array_shift($crawlarray);
  echo "<br>CRAWL ARRAY: ";
  print_r($crawlarray);
  echo "<br>END<br>";
  foreach ($crawlarray as $site){
    follow_links($site, $depth-1);
  }

}

follow_links($userLink, 1);

?>
