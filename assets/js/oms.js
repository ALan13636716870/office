/**
 * Created by gaosk on 15/12/21.
 */
/*弹出层*/
/*
 参数解释：
 title  标题
 url    请求的url
 id	    需要操作的数据id
 w      弹出层宽度（缺省调默认值）
 */
function layer_show(title,url,w,h){
  if (title == null || title == '') {
      title=false;
  };
  if (url == null || url == '') {
      url="404.html";
  };
  if (w == null || w == '') {
      w=500;
  };
  layer.open({type:2,title:title,shadeClose:false,shade:0.3,scrollbar:false,
    area:w+'px',offset:'100px',content:url,success:function(layero,index){
      layer.iframeAuto(index);
  }});
}
/*关闭弹出框口*/
function layer_close(){
    var index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
}

function nl2br (str, is_xhtml){
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)+/g, '$1'+ breakTag +'$2');
}

function formValidate(id){
  $("#"+id).validate({
    errorPlacement: function(error, element) {  //error[0].outerHTML
      var obj = $(element).closest(".form-group").find("label:eq(0)");
      obj.tooltip('show');
    },
    success: function(error, element){
      var obj = $(element).closest(".form-group").find("label:eq(0)");
      obj.tooltip('hide');
    }
  });
}