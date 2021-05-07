(function ($) {
  /**
   * Skip
   */
  $(document).ready(function () {
    $('#skip-1').on('click', function () {
      $('#localization').slideUp(400, function () {
        $('#categories').slideDown();
      });
    });
  });

  $(document).ready(function () {
    $('#skip-2').on('click', function () {
      $('#categories').slideUp(400, function () {
        $('#posts-personalize').slideDown();
      });
    });
  });

  $(document).ready(function () {
    $('#skip-3').on('click', function () {
      $('#posts-personalize').slideUp(400, function () {
        $('#emotions').slideDown();
      });
    });
  });

  $(document).ready(function () {
    $('#skip-4').on('click', function () {
      $('#emotions').slideUp(400, function () {
        $('#thankyou').slideDown();
      });
    });
  });
  /**
   * Add Location
   */
  $(document).ready(function () {
    $('#next-1').on('click', function () {
      var location = $('#personalize-city').val();
      var user = $(this).data('user');
      $.ajax({
        type: 'post',
        url: ajax_personalizer.url,
        data: {
          action: ajax_personalizer.action,
          _ajax_nonce: ajax_personalizer._ajax_nonce,
          personalizer: ajax_personalizer.personalizer,
          user: user,
          location: location
        },
        success: function (res) {
          if (res.success) {
            $('#localization').slideUp(400, function () {
              $('#categories').slideDown();
            });
          }
        },
        error: function (res) {
          console.log(res);
        }
      });
    });
  });
  /**
   * Add categories
   */
  $(document).ready(function () {
    $('#next-2').on('click', function () {
      var user = $(this).data('user');
      var tags = $('.categorie:checked')
        .map(function () {
          return this.value;
        })
        .get();
      
      if (tags.length > 0) {
        $.ajax({
          type: 'post',
          url: ajax_personalizer.url,
          data: {
            action: ajax_personalizer.action,
            _ajax_nonce: ajax_personalizer._ajax_nonce,
            personalizer: ajax_personalizer.personalizer,
            user: user,
            tags: tags
          },
          success: function (res) {
            //console.log(res);
            if (res.success) {
              $('#categories').slideUp(400, function () {
                $('#posts-personalize').slideDown();
              });
            }
          },
          error: function (res) {
            console.log(res);
          }
        });
      }
    });
  });
  /**
   * Add Post
   */
   $(document).ready(function () {
    $('#next-3').on('click', function () {
      var user = $(this).data('user');
      var tax = $('.post-item:checked')
        .map(function () {
          return this.value;
        })
        .get();
    
      if (tax.length > 0) {
        $.ajax({
          type: 'post',
          url: ajax_personalizer.url,
          data: {
            action: ajax_personalizer.action,
            _ajax_nonce: ajax_personalizer._ajax_nonce,
            personalizer: ajax_personalizer.personalizer,
            user: user,
            tax:tax
          },
          success: function (res) {
            if (res.success) {
              $('#posts-personalize').slideUp(400, function () {
                $('#emotions').slideDown();
              });
            }
          },
          error: function (res) {
            console.log(res);
          }
        });
      }
    });
  });
  /**
   * Add emotion
   */
   $(document).ready(function () {
    $('#next-4').on('click', function () {
      var user = $(this).data('user');
     
        var authors = $('.photo:checked').map(function(){
          return this.value;
        }).get();

      if ( authors.length > 0) {
        $.ajax({
          type: 'post',
          url: ajax_personalizer.url,
          data: {
            action: ajax_personalizer.action,
            _ajax_nonce: ajax_personalizer._ajax_nonce,
            personalizer: ajax_personalizer.personalizer,
            user: user,
            authors:authors
          },
          success: function (res) {
            if (res.success) {
              $('#emotions').slideUp(400, function () {
                $('#thankyou').slideDown();
              });
            } else {
                alert(res.data);
            }
          },
          error: function (res) {
            console.log(res);
          }
        });
      }
    });
  });
})(jQuery);
