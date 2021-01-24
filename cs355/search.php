<?php
try {
    $pdo = new PDO('mysql:host = 149.4.211.180; dbname=shas2679', 'shas2679', 'CS355');
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
}
catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
}

$search = $_GET['q'];

echo "<br>QUERY: ".$search."<br>";

$searchq = explode(" ",$search);
$i =0;
$construct="";
$params = array();
foreach ($searchq as $term){
  $i++;
  if($i ==1){
    $construct .= "title LIKE CONCAT('%',:search$i ,'%') OR description LIKE CONCAT('%',:search$i ,'%')";
  }
  else{
    $construct .= " AND title LIKE CONCAT('%',:search$i ,'%') OR description LIKE CONCAT('%',:search$i ,'%')";
  }

  $params[":search$i"] = $term;

}

$results = $pdo->prepare("SELECT * FROM `page` WHERE $construct");
$results -> execute($params);

if($results->rowCount() >0){
  echo $results->rowCount()." results found. <hr />";
}
else{
  echo "0 results found! <hr />";
}

echo "<pre>";
$dom = new DOMDocument();
$dom->loadHTML("results3.html");
$div = $dom->getElementById('searchResults');
$string = "";
echo "<section class='searchResults' id='searchResults'>";
foreach($results->fetchAll() as $result){
  echo $result['title']."\n";
  $string .= "<div>".$result['title']."</div>";
  if($result['description'] == ""){
    echo "There is no description available.\n";
    $string .= "<div>There is no description available.</div>";
  }
  else{
    echo $result['description']."\n";
    $string .="<div>".$result['description']."</div>";
  }
  echo $result['url']."\n";
  $string .=  "<div>".$result['url']."</div>";
  echo "<hr />";
}
echo "</section>";
$div->innerHTML = $string;

?>
