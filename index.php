<?
include_once "setting.php";
$base64 = file_get_contents("site_data.json");
?>
<!doctype html><html><head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><? echo $name_title; ?></title>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="design.css" type="text/css" />
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  var json_data = '<? echo $base64; ?>';
  var arr_color = [
		"blue.gif",
		"pink.gif",
		"orange.gif",
		"green.gif",
		"purple.gif",
	];
  var cnt_color = 0;
  function MakeLink(name,url){ return '<li><a href="'+url+'" target="_blank">'+name+'</a></li>'; }
  function MakePortlet(data){
    var ret = '<div class="portlet"><div class="portlet-header" style="background-image:url('+"'addition/"+arr_color[cnt_color++%5]+"'"+')">'+data[0]+'</div><div class="portlet-content"><ul id="bookmark" class="connectedSortable">';
    for(var i=1;i<data.length;++i) ret = ret + MakeLink(data[i][0],data[i][1]);
    return ret + '</ul></div></div>';
  }
  function ApplyEffects(){
    $( ".column" ).addClass( "ui-corner-all" );
    $( ".portlet" )
      .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
      .find( ".portlet-header" ).addClass( "ui-widget-header ui-corner-all" );
    $( "ul, li" ).disableSelection();
  }
  function MakeContents(json_data){
    var contents = "";
    for(var i=0;i<json_data.length;++i){
      var column = '<div class="column">';
      for(var j=0;j<json_data[i].length;++j) column = column + MakePortlet(json_data[i][j]);
      contents = contents + column + '</div>';
    }
    $($(".total_frame")[0]).html(contents);
    ApplyEffects();
  }
  </script>
  </head><body><br><center><h1><? echo $name_title; ?></h1></center><br><div class="total_frame"></div>
  <script>
  MakeContents(JSON.parse(json_data));
  </script>
  </body></html>
