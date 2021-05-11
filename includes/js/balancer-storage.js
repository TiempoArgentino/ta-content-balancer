(function ($) {
  $(document).ready(function () {
    
    if (localStorage.getItem("balancer_data") === "null") {
      localStorage.removeItem("balancer_data");
      $.ajax({
        type: "post",
        url: balancer_anon_ajax.url,
        data: {
          action: balancer_anon_ajax.action,
          _ajax_nonce: balancer_anon_ajax._ajax_nonce,
          anon: balancer_anon_ajax.anon,
          post_id: balancer_anon_ajax.post_id
        },
        success: function (res) {
          //console.log(res);
          if (res !== null) {
            localStorage.setItem("balancer_data", res);
          }
        },
        error: function (res) {
          //console.log(res);
        }
      }).done(function(){
        $.ajax({
          type: "post",
          url: balancer_front_data_ajax.url,
          data: {
            action: balancer_front_data_ajax.action,
            _ajax_nonce: balancer_front_data_ajax._ajax_nonce,
            balancer_get: balancer_front_data_ajax.balancer_get,
            balancer_data: JSON.parse(localStorage.getItem("balancer_data"))
          },
          success: function(res){
           // console.log(res);
            $('#your_interests').html(res);
          },
          error: function(res){
            //console.log(res);
          }
        });
      });
    } else {
      console.log('estoy en el segundo');
      if(localStorage.getItem("balancer_data") === "null") {
        localStorage.removeItem("balancer_data");
      }
      $.ajax({
        type: "post",
        url: balancer_anon_ajax_get.url,
        data: {
          action: balancer_anon_ajax_get.action,
          _ajax_nonce: balancer_anon_ajax_get._ajax_nonce,
          data: balancer_anon_ajax_get.data,
          storage: JSON.parse(localStorage.getItem("balancer_data")),
          post_id: balancer_anon_ajax_get.post_id
        },
        success: function (res) {
          if (res !== null) {
            localStorage.setItem("balancer_data", res);
          }
        },
        error: function (res) {
          // console.log(res);
        }
      }).done(function(){
        $.ajax({
          type: "post",
          url: balancer_front_data_ajax.url,
          data: {
            action: balancer_front_data_ajax.action,
            _ajax_nonce: balancer_front_data_ajax._ajax_nonce,
            balancer_get: balancer_front_data_ajax.balancer_get,
            balancer_data: JSON.parse(localStorage.getItem("balancer_data"))
          },
          success: function(res){
           // console.log(res);
            $('#your_interests').html(res);

          },
          error: function(res){
           // console.log(res);
          }
        });
      });
    }
  });
})(jQuery);
