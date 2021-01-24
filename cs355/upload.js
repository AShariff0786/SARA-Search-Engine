function append(c, result, title, url, desc) {
  result = "";
  result += "<br>\n<input type='checkbox' class='itemCheck' id='check_" + c + "'>\n<div class='result'>\n<ul>\n<li><h2>" + title + "</h2></li>\n";
  result += "<li><a href='" + url + "' class='ref'>" + url + "</a></li>\n";
  result += "<li class='desc'>" + desc + "</li>\n</ul>\n</div>";
  return result;
}

function selectedFiles() {
  var files = document.getElementById("usrfile").files;
  for (var i = 0, f; f = files[i]; i++) {
    var ext = f.name.split('.').pop();
    if (ext != "json" && ext != "csv" && ext != "xml") {

      return;
    }
    if (ext == "json") {
      var reader = new FileReader();
        reader.onload = (function(theFile) {
          return function(e) {
            var data = JSON.parse(e.target.result);
            var pages = data.pages;
            var items = "";
            for(var i = 0; i < pages.length; i++) {
              items += append(i, items, pages[i].title, pages[i].url, pages[i].desc);
            }
            items += "<br>";
            document.getElementById("searchResults").innerHTML = items;
          };
        })(f);
      reader.readAsText(f);
    } else if (ext == "csv") {
      var reader = new FileReader();
        reader.onload = (function(theFile) {
          return function(e) {
            var data = e.target.result.split("\n");
            var items = "";
            for(var i = 0; i < data.length - 1; i++) {
              var split = data[i].split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/);
              var title = split[0].replace(/['"]+/g, '');
              var url = split[1].replace(/['"]+/g, '');
              var desc = split[2].replace(/['"]+/g, '');
              items += append(i, items, title, url, desc);
            }
            items += "<br>";
            document.getElementById("searchResults").innerHTML = items;
          };
        })(f);
      reader.readAsText(f);
    } else if (ext == "xml") {
      var reader = new FileReader();
        reader.onload = (function(theFile) {
          return function(e) {
            var parser = new DOMParser();
            var parsedData = parser.parseFromString(e.target.result, "application/xml");
            var data = parsedData.getElementsByTagName("page");
            var items = "";
            for(var i = 0; i < data.length; i++) {
              var title = data[i].getElementsByTagName("title")[0].innerHTML;
              var url = data[i].getElementsByTagName("url")[0].innerHTML;
              var desc = data[i].getElementsByTagName("desc")[0].innerHTML;
              items += append(i, items, title, url, desc);
            }
            items += "<br>";
            document.getElementById("searchResults").innerHTML = items;
          };
        })(f);
      reader.readAsText(f);
    }
  }
}
function down() {
  var x = document.getElementById("exttype").value;
  if (x == "json") {
    downloadFiles("json");
  } else if (x == "csv") {
    downloadFiles("txt");
  } else if (x == "xml") {
    downloadFiles("xml");
  }
}

function downloadFiles(type) {
  var check = document.getElementsByClassName('itemCheck');
  var selected = new Array();
  for(var i = 0; i <check.length; i++) {
    if (check[i].checked == true) {
      selected.push(check[i].id);
    }
  }
  if (selected.length == 0) {
    return;
  }
  if (type == "json") {
    var data = {"pages": []};
    for(var j = 0; j <selected.length; j++) {
      var x = document.getElementById(selected[j]);
      var title = x.nextSibling.nextElementSibling.childNodes[1].children[0].innerText;
      var url = x.nextSibling.nextElementSibling.childNodes[1].children[1].innerText;
      var desc = x.nextSibling.nextElementSibling.childNodes[1].children[2].innerText;
      data.pages.push({"title": title, "url": url, "desc": desc});
    }
    data = JSON.stringify(data);
    var blob = new Blob([data], {type: "application/json;charset=utf-8"});
    saveAs(blob, "data.json");
  } else if (type == "txt") {
    var data = "";
    for(var j = 0; j <selected.length; j++) {
      var x = document.getElementById(selected[j]);
      var title = x.nextSibling.nextElementSibling.childNodes[1].children[0].innerText;
      var url = x.nextSibling.nextElementSibling.childNodes[1].children[1].innerText;
      var desc = x.nextSibling.nextElementSibling.childNodes[1].children[2].innerText;
      data += '"' + title + '","'+ url + '","' + desc + '"' + '\n';
    }
    var blob = new Blob([data], {type: "text/csv;charset=utf-8"});
    saveAs(blob, "data.csv");
  } else if (type == "xml") {
    var data = "<?xml version='1.0' encoding='UTF-8'?><root>";
    for(var j = 0; j <selected.length; j++) {
      var x = document.getElementById(selected[j]);
      var title = x.nextSibling.nextElementSibling.childNodes[1].children[0].innerText;
      var url = x.nextSibling.nextElementSibling.childNodes[1].children[1].innerText;
      var desc = x.nextSibling.nextElementSibling.childNodes[1].children[2].innerText;
      data += "<page>\n<desc>" + desc + "</desc>\n<title>" + title + "</title>\n<url>" + url + "</url>\n</page>\n"
    }
    data += "</root>";
    var blob = new Blob([data], {type: "text/plain;charset=utf-8"});
    saveAs(blob, "data.xml");
  }
}

function checkAll(){
  var check = document.getElementsByClassName('itemCheck');
  for(var i = 0; i <check.length; i++) {
    if (check[i].checked == false) {
      check[i].checked=true;
    }
  }
}
function checkNone(){
  var check = document.getElementsByClassName('itemCheck');
  for(var i = 0; i <check.length; i++) {
    if (check[i].checked == true) {
      check[i].checked=false;
    }
  }
}

function google() {
  var apiKey = 'AIzaSyCDtDsLQPzptQpsJA6bOtkrOFSUavoE7Hg';
  var cx = '002470078567593161575:qyh-cut4sto';
  var query = document.getElementById('searchbar').value;
  var items = "";
    $.get('https://www.googleapis.com/customsearch/v1?key=' + apiKey + '&cx=' + cx + '&q=' + query, function(data){
    for(var i = 0; i < 8; i++) {
      items += append(i, items, data.items[i].title, data.items[i].link, data.items[i].snippet);
    }
    items += "<br>";
    document.getElementById("searchResults").innerHTML = items;
  });
}
