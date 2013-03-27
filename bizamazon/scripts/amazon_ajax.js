function GetXmlHttpObject()
{var xmlHttp=null;
try
  {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
catch (e)
  {
  // Internet Explorer
  try
    {
    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
  catch (e)
    {
    xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
return xmlHttp;
}

function stateChanged()
{
if (xmlHttp.readyState==4)
{
document.getElementById("Amazon_Area").innerHTML=xmlHttp.responseText;
}
}

function getBookByCode() {
var isbn = document.F1.T1.value;
xmlHttp=GetXmlHttpObject();
if (xmlHttp==null) {
    alert("Your browser does not support AJAX!");
    return;
}
var url="http://bizkosen.tk/bizamazon/Sample_ajax.php";
url = url+"?code=ISBN&no="+isbn;
xmlHttp.onreadystatechange=stateChanged;
xmlHttp.open("GET",url,true);
xmlHttp.send(null);
}
        