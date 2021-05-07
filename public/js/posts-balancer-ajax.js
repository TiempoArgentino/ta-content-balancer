(function($){
    $(document).on('click','.save_favorite',function(e){
        e.preventDefault();
        var user_id = $(this).data('user');
        var post_id = $(this).data('post');

        $.ajax({
            type:'post',
            url: ajax_add_favorite.url,
            data:{
                action: ajax_add_favorite.action,
                _ajax_nonce: ajax_add_favorite._ajax_nonce,
                add_favorite: ajax_add_favorite.add_favorite,
                post_id:post_id,
                user_id:user_id
            },
            success: function(res){
                alert(res.data);
            },
            error: function(res){
                console.log(res);
            }
        });
    });

    $(document).ready(function(){
        $('.close-btn').on('click',function(){
            var user_id = $(this).data('user');
            var id_post = $(this).data('post');

            $.ajax({
                type:'post',
                url: ajax_delete_favorite.url,
                data:{
                    action: ajax_delete_favorite.action,
                    _ajax_nonce: ajax_delete_favorite._ajax_nonce,
                    fav_delete: ajax_delete_favorite.fav_delete,
                    id_post:id_post ,
                    user_id:user_id
                },
                success: function(res){
                    if(res.success) {
                        alert(res.data);
                        window.location.reload();
                    }
                },
                error: function(res){
                    console.log(res);
                }
            });
        });
    });
})(jQuery);