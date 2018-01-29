(function($){
  $.fn.uploader = function(settings){
    // 默认值
    settings=$.extend({
      container: null,
      callback: null
    },settings);

    var up = WebUploader.create({
      swf: 'assets/js/ossUploader/uploader.swf',
      server: upload_url,
      pick: this.selector,
      resize: false,
      accept: {
        title: '所有文件',
        extensions: '*'
      },
      fileNumLimit:10000,
      thumb: false,
      compress: false,
      formData: {
      }
    });

    var container_obj = $(this);
    var list_obj = $('<ul />').attr('class', 'webuploader-list');
    var list_files = {};
    if(settings.container != null){
      settings.container.append(list_obj);
    }else{
      $(this).append(list_obj);
    }

    // 当有文件被添加进队列的时候
    up.on( 'fileQueued', function( file ) {
      list_files[file.id] = file;
      var item_obj = createItem(file.id);
      var status_obj = $('<div />').attr('class', 'webuploader-status cl');
      $(item_obj).append(status_obj);

      var filename_obj = $('<span />').attr('class', 'webuploader-filename')
        .html(getFileShortName(file.name));
      var progress_obj = $('<span />').attr('class', 'webuploader-progress').html('(开始上传)');
      $(status_obj).append(filename_obj).append(progress_obj);

      up.upload();
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

      var item_obj = createItem(file.id);
      var file_info = response.msg;
      if(response.status == '1'){
        item_obj.append('<span>'+file_info.savename+' (<em class="text-success">上传成功</em>)</span>');
      }else{
        item_obj.append('<span>'+file.name+' (<em class="text-danger">'+file_info+'</em>)</span>');
      }
      item_obj.find('.webuploader-status').hide();

      if(typeof(settings.callback) == 'function'){
        settings.callback(file_info, item_obj);
      }
    });

    //文件上传失败
    up.on( 'uploadError', function( file ) {
      $('#webuploader-item-'+file.id).find('.webuploader-progress').html('(上传出错)');
    });

    //上传完后,不管成功或者失败
    up.on( 'uploadComplete', function( file ) {
    });

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