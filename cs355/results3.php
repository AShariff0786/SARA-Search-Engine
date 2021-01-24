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
  <div class= "aboutsec">
    <p id="uploadinfo">
      <div class= "uploadform">
        <button id="selectall" onclick="checkAll()">Select All</button>
        <button id="delselct" onclick="checkNone()">Deselect All</button>
	       <button id="downloadbtn" onclick="down()">Download</button>
	       <select name= "exttype" id="exttype">
	          <option value="json">JSON</option>
	          <option value="csv">CSV</option>
	          <option value="xml">XML</option>
	       </select>
      </div>
      <section class="searchResults" id="searchResults">
      <?php
        $startTime = microtime();
        try {
            $pdo = new PDO('mysql:host = 149.4.211.180; dbname=shas2679', 'shas2679', 'CS355');
            // set the PDO error mode to exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e){
        }

        $searchType =  $_GET['searchType'] ;
        $search = $_GET['q'];
        $count = -1;

        if($searchType == "partial"){
          $searchq = explode(" ",$search);
          $i =0;
          $construct="";
          $params = array();
          foreach ($searchq as $term){
            $i++;
            if($i ==1){
              $construct .= '%'.$term;
            }
            else{
              $construct .= '%'.$term;
            }
            $construct .= '%';
          }
          try{
            $results = $pdo->prepare("SELECT * FROM `page`, `word`, `page_word` WHERE page.pageId = page_word.pageId AND word.wordId = page_word.wordId AND word.wordName LIKE '".$construct."' ORDER BY freq DESC");
            $results -> execute();
          }
          catch(Exception $e){
            echo "Failed: " . $e->getMessage();
          }
          if($results->rowCount() >0){
            echo "<div id='results3'>".$results->rowCount()." results found. </div><br><hr/>";
          }
          else{
            echo "<div id='results3'>0 results found!</div><br><hr/>";
          }

          foreach($results->fetchAll() as $result){
            $count = $count + 1;
            echo "<input type='checkbox' class='itemCheck' id='check_".$count."'>";
            echo "<div class='result'>";
            echo "<ul>\n";
            echo "<li>\n";
            echo "<h2 id='title_".$count."'>".$result['title']."</h2></li>\n";
            echo "<li><a href='".$result['url']."' class='ref' id='url_".$count."'>".$result['url']."</a></li>\n";
            if($result['description'] == ""){
              echo "<li class='desc' id='desc_".$count."'>There is no description available.</li>\n</ul>\n</div>\n";
            }
            else{
              echo "<li class='desc' id='desc_".$count."'>".$result['description']."</li>\n</ul>\n</div>\n";
            }
            echo "<br>";
          }
          $endTime = microtime();
          $totalTime = $endTime - $startTime;
          $mydate=getdate();
          $month = date("m", strtotime($mydate[month]));
          $searchDate = "$mydate[year]-$month-$mydate[mday]";
          try{
            $searchParams = array (':terms' => $search, ':count' => $results-> rowCount(), ':searchDate' => $searchDate, ':timeToSearch' => $totalTime);
            $result = $pdo->prepare("INSERT INTO `search` (terms, count, searchDate, timeToSearch) VALUES (:terms, :count , :searchDate, :timeToSearch)");
            $result = $result->execute($searchParams);
          }
          catch(Exception $e){
            echo "Insert search Table failed: " . $e->getMessage();
          }
        }

        else if($searchType == "insensitive"){
          $searchq = explode(" ",$search);
          $i =0;
          $construct="";
          $params = array();
          foreach ($searchq as $term){
            $i++;
            if($i ==1){
              $construct .= $term;
            }
            else{
              $construct .= $term." ";
            }
          }
          try{
            $results = $pdo->prepare("SELECT * FROM `page`, `word`, `page_word` WHERE page.pageId = page_word.pageId AND word.wordId = page_word.wordId AND Upper(word.wordName) = Upper('".$construct."') ORDER BY freq DESC");
            $results -> execute();
          }
          catch(Exception $e){
            echo "<div>Failed: " . $e->getMessage()."</div>";
          }
          if($results->rowCount() >0){
            echo "<div id='results3'>".$results->rowCount()." results found. </div><br><hr/>";
          }
          else{
            echo "<div id='results3'>0 results found!</div><br><hr/>";
          }

          foreach($results->fetchAll() as $result){
            $count = $count + 1;
            echo "<input type='checkbox' class='itemCheck' id='check_".$count."'>";
            echo "<div class='result'>";
            echo "<ul>\n";
            echo "<li>\n";
            echo "<h2 id='title_".$count."'>".$result['title']."</h2></li>\n";
            echo "<li><a href='".$result['url']."' class='ref' id='url_".$count."'>".$result['url']."</a></li>\n";
            if($result['description'] == ""){
              echo "<li class='desc' id='desc_".$count."'>There is no description available.</li>\n</ul>\n</div>\n";
            }
            else{
              echo "<li class='desc' id='desc_".$count."'>".$result['description']."</li>\n</ul>\n</div>\n";
            }
            echo "<br>";
          }
          $endTime = microtime();
          $totalTime = $endTime - $startTime;
          $mydate=getdate();
          $month = date("m", strtotime($mydate[month]));
          $searchDate = "$mydate[year]-$month-$mydate[mday]";
          try{
            $searchParams = array (':terms' => $search, ':count' => $results-> rowCount(), ':searchDate' => $searchDate, ':timeToSearch' => $totalTime);
            $result = $pdo->prepare("INSERT INTO `search` (terms, count, searchDate, timeToSearch) VALUES (:terms, :count , :searchDate, :timeToSearch)");
            $result = $result->execute($searchParams);
          }
          catch(Exception $e){
            echo "Insert search Table failed: " . $e->getMessage();
          }
        }
        else {
          $searchq = explode(" ",$search);
          $i =0;
          $construct="";
          $params = array();
          foreach ($searchq as $term){
            $i++;
            if($i ==1){
              $construct .= '%'.mb_strtoupper($term);
            }
            else{
              $construct .= '%'.mb_strtoupper($term);
            }
            $construct .= '%';
          }
          try{
            $results = $pdo->prepare("SELECT * FROM `page`, `word`, `page_word` WHERE page.pageId = page_word.pageId AND word.wordId = page_word.wordId AND Upper(word.wordName) LIKE Upper('".$construct."') ORDER BY freq DESC");
            $results -> execute();
          }
          catch(Exception $e){
            echo "Failed: " . $e->getMessage();
          }
          if($results->rowCount() >0){
            echo "<div id='results3'>".$results->rowCount()." results found. </div><br><hr/>";
          }
          else{
            echo "<div id='results3'>0 results found!</div><br><hr/>";
          }

          foreach($results->fetchAll() as $result){
            $count = $count + 1;
            echo "<input type='checkbox' class='itemCheck' id='check_".$count."'>";
            echo "<div class='result'>";
            echo "<ul>\n";
            echo "<li>\n";
            echo "<h2 id='title_".$count."'>".$result['title']."</h2></li>\n";
            echo "<li><a href='".$result['url']."' class='ref' id='url_".$count."'>".$result['url']."</a></li>\n";
            if($result['description'] == ""){
              echo "<li class='desc' id='desc_".$count."'>There is no description available.</li>\n</ul>\n</div>\n";
            }
            else{
              echo "<li class='desc' id='desc_".$count."'>".$result['description']."</li>\n</ul>\n</div>\n";
            }
            echo "<br>";
          }
          $endTime = microtime();
          $totalTime = $endTime - $startTime;
          $mydate=getdate();
          $month = date("m", strtotime($mydate[month]));
          $searchDate = "$mydate[year]-$month-$mydate[mday]";
          try{
            $searchParams = array (':terms' => $search, ':count' => $results-> rowCount(), ':searchDate' => $searchDate, ':timeToSearch' => $totalTime);
            $result = $pdo->prepare("INSERT INTO `search` (terms, count, searchDate, timeToSearch) VALUES (:terms, :count , :searchDate, :timeToSearch)");
            $result = $result->execute($searchParams);
          }
          catch(Exception $e){
            echo "Insert search Table failed: " . $e->getMessage();
          }
        }
      ?>
      </section>
    </p>
  </div>
  <script src= "uploadPhase5.js"></script>
  <script src= "FileSaver.js"></script>
</body>
</html>
