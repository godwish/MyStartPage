<?
include_once "setting.php";
$base64 = file_get_contents("site_data.json");
$locale = json_decode(file_get_contents("addition/locale_".$locale.".json"),true);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><? echo $name_title; ?></title>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="design.css" type="text/css" />
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  var json_data = '<? echo $base64; ?>';
  var dialog_category;
  var dialog_item;
  var obj_modify_category;
  var obj_modify_item;
  var is_drag = false;
  var time_push = 0;

  function IsClick(){
    if( !is_drag ) return true;
    var tm = new Date().getTime() - time_push;
    if( Date.now-time_push  < tm )  return true;
    return false;
  }
  function query(url,qry,cback){
    var	req	=	new XMLHttpRequest();
    req.open('POST',url,true);
    req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    req.onreadystatechange	=	cback;
    req.send(qry);
  }
  function c_back(){
    if(	this.readyState	!=	4	)	return;
    if(	this.status	!=	200	)	return;
    var result	=	this.responseText;
    if(	result	==	'0'	) return;
    alert("오류 - "+result);
  }
  function MakeLink(name,url){ return '<li><span class="site_item" data-url="'+url+'"><a href="#" onclick="ModifyItem(this);">'+name+'</a></span> <a href="#" onclick="DeleteItem(this);">[-]</a></li>'; }
  function MakePortlet(data){
    var ret = '<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"><div class="portlet-header ui-widget-header ui-corner-all"><a href="#" onclick="ModifyCategory(this);"><span class="site_category">'+data[0]+'</span></a> <a href="#" onclick="DeletePortlet(this);">[-]</a><a href="#" onclick="InsertItem(this);">[+]</a></div><div class="portlet-content"><ul id="bookmark" class="connectedSortable">';
    for(var i=1;i<data.length;++i) ret = ret + MakeLink(data[i][0],data[i][1]);
    return ret + '</ul></div></div>';
  }
  function ApplyEffects(){
    $( ".column" ).sortable(
      {
        connectWith: ".column",
        handle: ".portlet-header",
        cancel: ".portlet-toggle",
        placeholder: "portlet-placeholder ui-corner-all",
        start: function(){ time_push = new Date().getTime(); is_drag=true; },
        stop: function(){ is_drag=false; }  
      }
    );
    InitElement();
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
  function InsertItem(obj){
    if(!IsClick()) return;
    var uls = $(obj).parent().parent().find("ul")[0];
    var data = $(uls).html()+MakeLink("No Name","");
    $(uls).html(data);
  }
  function DeletePortlet(obj){
    if(!IsClick()) return;
    $(obj).parent().parent().remove();
  }
  function DeleteItem(obj){
    if(!IsClick()) return;
    $(obj).parent().remove();
  }
  function InitElement(){
    $( "#bookmark, #bookmark2" ).sortable(
      {
        connectWith: ".connectedSortable",
        revert: true,
        placeholder: "item-placeholder ui-corner-all",
        start: function(){ time_push = new Date().getTime(); is_drag=true },
        stop: function(){ is_drag=false; } 
      }
    );
    $( "ul, li" ).disableSelection();
  }
  function InsertPortlet(){
    $( $("body").find(".column")[0] ).append(MakePortlet(new Array("New Category")));
    InitElement();
  }
  function Save(){
    var columns = $("body").find(".column");
    var data = new Array();
    for(var i=0;i<columns.length;++i){
      var portlets = $(columns[i]).find(".portlet");
      data[i] = new Array();
      for(var j=0;j<portlets.length;++j){
        var str_category = $($(portlets[j]).find("span")[0]).html();
        data[i][j] = new Array();
        data[i][j][0] = str_category;
        var lis = $(portlets[j]).find('li');
        for(var c=0;c<lis.length;++c){
          var item = $(lis[c]).find('span');
          data[i][j][c+1] = new Array();
          data[i][j][c+1][0] = $($(item).find('a')[0]).html();
          data[i][j][c+1][1] = $(item).data("url");
        }
      }
    }
    //alert(JSON.stringify(data));
    query("site_save.php","data="+JSON.stringify(data),c_back);
  }
  function ModifyCategory(obj_cat){
    if(!IsClick()) return;
    obj_modify_category = $(obj_cat).find("span")[0];
    var cat_name = $(obj_modify_category).html();
    $($(dialog_category).find('input')[0]).val(cat_name);
    dialog_category.dialog( "open" );
  }
  function ConfirmCategory(){
    var modify_str = $($(dialog_category).find('input')[0]).val();
    $(obj_modify_category).html(modify_str);
    dialog_category.dialog( "close" );
  }
  function ModifyItem(obj_item){
    if(!IsClick()) return;
    obj_modify_item = obj_item;
    var name = $(obj_modify_item).html();
    var obj_span = $(obj_modify_item).parent();
    var url = obj_span.data("url");
    $($(dialog_item).find('input')[0]).val(name);
    $($(dialog_item).find('input')[1]).val(url);
    dialog_item.dialog( "open" );
  }
  function ConfirmItem(){
    var modify_str = $($(dialog_item).find('input')[0]).val();
    var modify_url = $($(dialog_item).find('input')[1]).val();
    $(obj_modify_item).html(modify_str);
    var obj_span = $(obj_modify_item).parent();
    obj_span.data("url",modify_url);
    dialog_item.dialog( "close" );
  }
  function InitDialog(){
    dialog_category = $( "#dialog_category" ).dialog({
      autoOpen: false,
      width: 350,
      height: 200,
      modal: true,
      buttons: {
        "<? echo $locale['category_btn_confirm'] ?>" : ConfirmCategory,
        "<? echo $locale['category_btn_cancel'] ?>" : function() { dialog_category.dialog( "close" ); }
      },
      close: function() { dialog_category.dialog( "close" ); }
    });
    $("#dialog_category").keydown(function (event) {
        if (event.keyCode == $.ui.keyCode.ENTER) {
          ConfirmCategory();
          return false;
        }
    });
    dialog_item = $( "#dialog_item" ).dialog({
      autoOpen: false,
      width: 350,
      height: 250,
      modal: true,
      buttons: {
        "<? echo $locale['site_btn_confirm'] ?>" : ConfirmItem,
        "<? echo $locale['site_btn_cancel'] ?>" : function() { dialog_item.dialog( "close" ); }
      },
      close: function() { dialog_item.dialog( "close" ); }
    });
    $("#dialog_item").keydown(function (event) {
        if (event.keyCode == $.ui.keyCode.ENTER) {
          ConfirmItem();
          return false;
        }
    });
  }
</script>
</head>
<body>
<div id="dialog_category" title="<? echo $locale['category_edit'] ?>">
  <form>
    <fieldset>
      <label for="name"><? echo $locale['category_name'] ?></label>
      <input type="text" name="value_category" id="value_category" value="" class="text ui-widget-content ui-corner-all">
    </fieldset>
  </form>
</div>
<div id="dialog_item" title="<? echo $locale['site_edit'] ?>">
  <form>
    <fieldset>
      <label for="name"><? echo $locale['site_name'] ?></label>
      <input type="text" name="value_item" id="value_item" value="" class="text ui-widget-content ui-corner-all">
      <label for="name"><? echo $locale['site_url'] ?></label>
      <input type="text" name="value_url" id="value_url"value="" class="text ui-widget-content ui-corner-all">
    </fieldset>
  </form>
</div>
  <br><center><h1><? echo $name_title; ?></h1><br>
  <div class="widget">
    <a class="ui-button ui-widget ui-corner-all" href="#" onclick='InsertPortlet();'><? echo $locale['category_add'] ?></a>
    <a class="ui-button ui-widget ui-corner-all" href="#" onclick='Save();'><? echo $locale['save'] ?></a>
  </div></center><br>
  <div class="total_frame"></div>
<script>
InitDialog();
MakeContents(JSON.parse(json_data));
</script>
</body>
</html>