(function($){
  $.fn.uploader = function(settings){
    // 默认值
    settings=$.extend({
      field: null,
      multi: false,
      initData: [],
      container: null,
      callback: null,
      delcallback: null
    },settings);

    var up = WebUploader.create({
      swf: 'assets/js/ossUploader/uploader.swf',
      server: oss_server,
      pick: this.selector,
      resize: false,
      accept: {
        title: '图片',
        extensions: 'gif,jpg,jpeg,bmp,png,tif'
      },
      thumb: false,
      compress: false,
      formData: {
      }
    });

    var multi_index = 0;
    var token_get = false;
    var container_obj = $(this);
    var list_obj = $('<ul />').attr('class', 'webuploader-list');
    var list_files = {};
    if(settings.container != null){
      settings.container.append(list_obj);
    }else{
      $(this).append(list_obj);
    }

    initItems();

    // 当有文件被添加进队列的时候
    up.on( 'fileQueued', function( file ) {

      list_files[file.id] = file;
      getToken();
      var item_obj = createItem(file.id);
      var status_obj = $('<div />').attr('class', 'webuploader-status cl');
      $(item_obj).append(status_obj);

      var filename_obj = $('<span />').attr('class', 'webuploader-filename')
        .html(getFileShortName(file.name));
      var progress_obj = $('<span />').attr('class', 'webuploader-progress').html('(开始上传)');
      $(status_obj).append(filename_obj).append(progress_obj);

      waitToken();
      function waitToken(){
        var fp = up.option('formData');
        if(fp.OSSAccessKeyId != undefined){
          fp['key'] = fp['keypre']+genNewFile(file.name);
          fp['Content-Type'] = file.type;
          list_files[file.id]['path'] = fp['key'];
          up.upload();
        }else{
          setTimeout(function(){waitToken();}, 20);
        }
      }
    });

    // 文件上传过程中创建进度条实时显示。
    up.on( 'uploadProgress', function( file, percentage ) {
      var item_obj = createItem(file.id);
      var text = '('+ Math.round(percentage*10000)/100 + '%)';
      item_obj.find('.webuploader-progress').html(text);
    });

    //文件上传成功
    up.on( 'uploadSuccess', function( file, response ) {
      var item_obj = createItem(file.id);

      item_obj.find('.webuploader-progress').html('(上传成功)');

      if(response._raw == ''){
        addShow(file.id, {'path':list_files[file.id]['path'],'uploaded':true});
      }
    });

    //文件上传失败
    up.on( 'uploadError', function( file ) {

      $('#webuploader-item-'+file.id).find('.webuploader-progress').html('(上传出错)');
    });

    //上传完后,不管成功或者失败
    up.on( 'uploadComplete', function( file ) {
    });

    //向展示区添加一张图片信息
    function addShow(fileid, imginfo){

      if(imginfo.path == null)return false;
      if(!settings.multi){
        list_obj.html('');
      }

      var item_obj = createItem(fileid);

      if(imginfo.path.substr(0,7)=="http://"){
        var imgurl = simgurl = imginfo.path;
      }else{
        var imgurl = oss_preview_api+'&path='+imginfo.path, simgurl = imgurl+'&width=100';
      }

      var img_obj = $('<a href="'+imgurl+'" target="_blank"><img src="'+imgurl+'"></a>');

      item_obj.append(img_obj);
      //var view_obj = $('<div />').attr('class', 'webuploader-view');
      //view_obj.append(img_obj);
      //item_obj.append(view_obj);


      if(settings.multi){
        multiImg(fileid, imginfo);
      }else{
        singleImg(fileid, imginfo);
      }

      item_obj.find('.webuploader-status').hide();

      if(typeof(settings.callback) == 'function'){
        settings.callback(imginfo, item_obj);
      }
    }

    //单图
    function singleImg(fileid, imginfo){
      var item_obj = createItem(fileid);

      item_obj.append('<button type="button" href="javascript:;" class="btn size-xs btn-my-del c-warning">X</button>');
      item_obj.find('.btn-my-del').click(function(){
        if(!confirm('你确定要删除？')){
          return false;
        }
        if(typeof(settings.delcallback) == 'function'){
          settings.delcallback(imginfo,item_obj);
        }
        item_obj.remove();
      });
    }

    //多图
    function multiImg(fileid, imginfo){
      var item_obj = createItem(fileid);

      item_obj.append('<button type="button" href="javascript:;" class="btn btn-xs btn-my-del c-warning">X</button>');
      item_obj.find('.btn-my-del').click(function(){
        if(!confirm('你确定要删除？')){
          return false;
        }
        if(typeof(settings.delcallback) == 'function'){
          settings.delcallback(imginfo,item_obj);
        }
        item_obj.remove();
      });

    }

    //获取七牛token
    function getToken(){
      if(!token_get){
        $.ajax({
          type: "GET",
          url: oss_token_api,
          dataType: "json",
          success: function(data){
            up.option('formData', data);
            token_get = true;
            return true;
          }
        });
      }
      return false;
    }

    //初始化数据
    function initItems(){
      if(settings.initData.length == 0)return false;
      if(settings.multi){
          $.each(settings.initData, function(i,v){
          v['uploaded'] = false;
          addShow(v.id+randomString(6),{'path':v, 'uploaded':false});
        });
      }else{
        addShow(container_obj.attr('id'),{'path':settings.initData, 'uploaded':false});
      }
    }

    //创建一个 item
    function createItem(fileid){
      var item_obj = $('#webuploader-item-'+fileid);
      if(item_obj.length == 0){
        var item_obj = $('<li />').attr('class', 'webuploader-item').attr('id', 'webuploader-item-'+fileid);
        list_obj.append(item_obj);
      }
      return item_obj;
    }

    //获取文件名简短信息
    function getFileShortName(filename){
      var suffix = filename.replace(/.+\./, "");
      var name = filename.substring(0, filename.lastIndexOf('.'));
      if(name.length > 20){
        name = name.substr(0, 17) + '..' + name.substr(-2);
      }
      return name+'.'+suffix;
    }

    //生成新的文件名
    function genNewFile(filename){
      var point = filename.lastIndexOf(".");
      var type = filename.substr(point);
      return randomString()+type;
    }

    //生成随机字符串
    function randomString(len) {
      len = len || 32;
      var $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
      var maxPos = $chars.length;
      var pwd = '';
      for (var i = 0; i < len; i++) {
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
      }
      return pwd;
    }
  }
})(jQuery);