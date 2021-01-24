function google() {
  var cx = '002470078567593161575:zqd-tzolzy4';
  var apiKey= 'AIzaSyCMqX1bnlZpRfVBGIgftWPeluaMLo99iCs';
  var gcse = document.createElement('script');
  var query = document.getElementById('searchbar').value;
  gcse.type = 'text/javascript';
  gcse.async = true;
  gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(gcse, s);
  var items = "";
  var query= 'https://www.googleapis.com/customsearch/v1?key=' + apiKey + '&cx=' + cx + '&q=' + query;
  var response= httpGet(query);
  hndlr(response[1]);
}
function add(data){
    for(var i = 0; i < data.items.length; i++) {
      items += addResult(i, items, data.items[i].title, data.items[i].formattedUrl, data.items[i].snippet);
    }
    items += "<br>";
    document.getElementById("results_info").innerHTML = items;
  });
function httpGet(theUrl)
{
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
    add()
    xmlHttp.send( null );
    return xmlHttp.responseText;
}
function hndlr(response) {
      for (var i = 0; i < response.items.length; i++) {
        var item = response.items[i];
        // in production code, item.htmlTitle should have the HTML entities escaped.
        document.getElementById("results_info").innerHTML += "<br>" + item.htmlTitle;
      }
}
