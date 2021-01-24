<!DOCTYPE html>
<html>
<head>
  <meta charset = "UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel = "stylesheet" href = "styl-res.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<title>SARA</title>
<nav>
  <div class = "topnavbar">
    <a href = "index.html" id="Home" > Home </a>
    <div class = "dropdown">
      <button class = "dropbtn" id="Course"> Course
        <div class = "downarrow">&#x25BE </div>
      </button>
      <div class = "dropcontent" >
        <a href = "https://learn.zybooks.com/zybook/CUNYCSCI355TeitelmanSpring2019">Zybook</a>
      </div>
   </div>
   <div class= "dropdown">
     <button class = "dropbtn" id= "Search">Search
       <div class = "downarrow"> &#x25BE </div>
     </button>
     <div class = "dropcontent" >
       <a href = "results.html">Phase 2: Fixed List</a>
       <a href = "upload.html">Phase 3: From File</a>
       <a href = "result2.html">Phase 4: Google API</a>
       <a href = "results3.php">Phase 5: My Search Engine</a>
     </div>
   </div>
   <div class = "dropdown">
     <button class = "dropbtn" id = "Browser">Browser
       <div class = "downarrow">&#x25BE</div>
     </button>
     <div class= "dropcontent">
       <a href= "browseinfo.html">Navigator Information</a>
       <a href= "windowinfo.html">Window Information</a>
       <a href= "screeninfo.html">Screen Information</a>
       <a href= "location.html">Location Information</a>
       <a href= "history.html">Histoy</a>
     </div>
   </div>
   <div class = "dropdown">
     <button class = "dropbtn" id = "About">About
       <div class = "downarrow">&#x25BE</div>
     </button>
     <div class= "dropcontent">
       <a href = "about.html">About Us</a>
       <a href = "contact.html">Contact Us</a>
     </div>
   </div>
   <a href = "admin.php" id="Home" > Admin </a>
  </div>
</nav>
<body>
  <img src= "IMG_1702.png">
  <div>
    <section class = "sec" >
      <div class= "searchsec">
        <form action="results3.php" method="GET">
           <input type= "text" id="searchbar" name="q" class="home" placeholder= "Search a term">
           <input type= "submit" name="search" id="SearchBtn" value="&#x1F50D">
           <select name= "searchType" id="searchtype" name="searchtype">
              <option value="insensitive">Case-Insensitive</option>
              <option value="partial">Allow Partial Match</option>
              <option value="both">Allow Partial Match and Case-Insensitive</option>
           </select>
        </form>
      </div>
    </section>
  </div>
  <section class = "adminFunction">
    <br><br>
    <form action= "admin.php" method="get">
      <div class="urlAdmin" id="urladmin">
        <input type= "text" id="adminBar" placeholder= "Enter a URL to be indexed">
        <input type= "submit" id="SearchBtn" value="&#x1F50D">
      </div>
    </form>
  </section>
  <?php
    $userLink = $_GET['url'];
    try {
      $pdo = new PDO('mysql:host = 149.4.211.180; dbname=shas2679', 'shas2679', 'CS355');
      // set the PDO error mode to exception
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e){
    }

    $already_crawled = array();
    $crawlarray;
    $already_added_word=array();


      try{
        $searchResults = $pdo->prepare("SELECT * FROM `search` ");
        $searchResults -> execute();
      }
      catch(Exception $e){
        echo "Failed: " . $e->getMessage();
      }
      if($searchResults->rowCount() > 0){
        echo "<table>\n<tr>\n<th> Terms </th>\n<th> Count </th>\n<th> Search Date </th>\n<th> Time To Search </th></tr>";
        foreach($searchResults->fetchAll() as $result){
          echo "<tr>\n<td>".$result['terms']."</td>\n<td>".$result['count']."</td>\n<td>".$result['searchDate']."</td>\n<td>".$result['timeToSearch']."</td></tr>";
        }
        echo "</table>";
      }
      try{
        $searchResults = $pdo->prepare("SELECT * FROM `search` ");
        $searchResults -> execute();
      }
      catch(Exception $e){
        echo "Failed: " . $e->getMessage();
      }
      if($searchResults->rowCount() > 0){
        echo "<section id='adminResults'>";
        foreach($searchResults->fetchAll() as $result){
          echo "<div>Term: ".$result['terms']."</div>";
          echo "<div>Number of Results: ".$result['count']."</div>";
          echo "<div>Time it Took: ".$result['timeToSearch']."</div><br>";
        }
        echo "</section>";
      }

    function get_text($url){
      $options = array('http' => array('method' => "GET", 'headers' => "User-Agent: SARABot\n"));
      $context = stream_context_create($options);
      $doc = new DOMDocument();
      @$doc-> loadHTML(@file_get_contents($url, false, $context));
      $details= json_decode(get_details($url));
      $list = $doc -> getElementsByTagName("p");
      $string = "";
      $length= $list -> length;
      for($i =0; $i < $length; $i++){
        $list = $doc -> getElementsByTagName("p");
        $list = $list -> item($i) -> nodeValue;
        $list = str_ireplace(","," ",$list);
        $list = str_ireplace("."," ",$list);
        $list = str_ireplace("!"," ",$list);
        if($list != ' ' || $list !=''){
          $string .= " ".$list." ";
        }
      }
      $title = $details->Title;
      $string .= " ".$title." ";
      $description = $details->Description;
      $string .= " ".$description." ";
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
        echo "<section id='crawlerResults'>URL was successfully indexed.</section>";
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
      foreach ($linklist as $link) {
        $l = $link ->getAttribute("href");
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

        if(!in_array($l, $already_crawled)){
          $already_crawled[] = $l;
          $crawlarray[] = $l;

          $details = json_decode(get_details($l));

          $text= get_text($l);
          $words = explode(" ",$text);
          $length = sizeof($words);
          try{
            $pageID = $pdo ->query("SELECT pageId FROM `page` WHERE url_hash='".md5($details->URL)."'");
            $pageID = $pageID ->fetchAll();
            $pageID = $pageID[0]['pageId'];
          }
          catch(Exception $e){
          }

          if($pageID != NULL){
            for($i =0; $i < $length; $i++){
              try{
                $wordID = $pdo ->query("SELECT wordId FROM `word` WHERE wordName='".$words[$i]."'");
                $wordID = $wordID ->fetchAll();

                $wordID = $wordID[0]['wordId'];

              }
              catch(Exception $e){

              }
              if($wordID==Null && !in_array($words[$i], $already_added_word)){
                $already_added_word[] = $words[$i];
                $wordParams = array (':wordName' => $words[$i]);
                try{
                  $result = $pdo->prepare("INSERT INTO `word` (wordName) VALUES ( :wordName)");
                  $result = $result->execute($wordParams);
                }
                catch(Exception $e){

                }
              }
              if ($wordID!=NULL){
                try{
                  $pagewordID = $pdo ->query("SELECT pageWordId FROM `page_word` WHERE pageId= '".$pageID."' AND wordId='".$wordID."'");
                  $pagewordID = $pagewordID ->fetchAll();
                  $pagewordID = $pagewordID[0]['pageWordId'];

                }
                catch(Exception $e){

                }
              if($pagewordID == NULL){
                try{
                  $pagewordParams = array (':pageID' => $pageID, ':wordID' => $wordID);
                  $result = $pdo->prepare("INSERT INTO `page_word` (pageId, wordId, freq) VALUES (:pageID, :wordID ,1)");
                  $result = $result->execute($pagewordParams);
                }
                catch(Exception $e){

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
                }
              }
            }
            }
          }

          $rows = $pdo ->query("SELECT * FROM `page` WHERE url_hash='".md5($details->URL)."'");
          $rows = $rows -> fetchColumn();


          $lastIndexed =date ("Y-m-d ", getlastmod());
          $accessTime = time();
          $indexTime = $accessTime - $currentTime;

          $lastModified = str_ireplace(" ","",$details->LastModified);
          $params = array (':url' => $details->URL,':title' => $details->Title, ':description' => $details->Description, ':lastModified' => $lastModified ,':lastIndexed' => $lastIndexed, ':timeToIndex' => $indexTime ,':url_hash' => md5($details->URL));

          if($rows > 0){
            if(!is_null($params[':title']) && !is_null($params[':description']) && !is_null($params[':title'] != '')){
              if(!empty($params[':lastModified'])){
                try{
                  $result = $pdo->prepare("UPDATE `page` SET url=:url, title=:title, description=:description, lastModified=:lastModified, lastIndexed=:lastIndexed, timeToIndex=:timeToIndex, url_hash=:url_hash WHERE url_hash=:url_hash");
                  $result = $result->execute($params);
                }
                catch(PDOException $e){
                }
              }
              else {
                try{
                  $result = $pdo->prepare("UPDATE `page` SET url=:url, title=:title, description=:description,  lastIndexed=:lastIndexed, timeToIndex=:timeToIndex, url_hash=:url_hash WHERE url_hash=:url_hash");
                  $result = $result->execute($params);
                }
                catch(PDOException $e){
                }
              }
            }
          }
          else{
            if(!is_null($params[':title']) && !is_null($params[':description']) && !is_null($params[':title'] != '')){
              if(!empty($params[':lastModified'])){
                $result = $pdo->prepare("INSERT INTO `page` (url, title, description, lastModified, lastIndexed, timeToIndex, url_hash) VALUES (:url, :title, :description, :lastModified, :lastIndexed, :timeToIndex, :url_hash)");
                $result = $result->execute($params);
              }
              else{
                $params = array (':url' => $details->URL,':title' => $details->Title, ':description' => $details->Description, ':lastIndexed' => $lastIndexed, ':timeToIndex' => $indexTime ,':url_hash' => md5($details->URL));
                try{
                  $result = $pdo->prepare("INSERT INTO `page` (url, title, description, lastIndexed, timeToIndex, url_hash) VALUES (:url, :title, :description, :lastIndexed, :timeToIndex, :url_hash)");
                  $result = $result->execute($params);
                }
                catch(PDOException $e){
                }
              }
            }
          }
      }
      }

      array_shift($crawlarray);
      foreach ($crawlarray as $site){
        follow_links($site, $depth-1);
      }
    }

    get_history();
    follow_links($userLink, 2);
  ?>
  <script src= "upload.js"></script>
  <script src= "FileSaver.js"></script>
</body>
</html>
