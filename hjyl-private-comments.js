/**
 * WP Private Comments
 * 
 * @author 皇家元林
 * @link https://hjyl.org/wp-private-comments
 */


console.log("\n %c WP Private Comments By 皇家元林 %c https://hjyl.org \n", "color: #fadfa3; background: #030307; padding:5px 0;", "background: #fadfa3; padding:5px 0;");
jQuery(document).ready(function($) {
    $('.pct-toggle-private').on('click', function(e) {
        e.preventDefault();
        
        var commentId = $(this).data('comment-id');
        
        $.ajax({
            type: 'POST',
            url: pct_ajax_object.ajax_url,
            data: {
                action: 'pct_toggle_private_comment',
                comment_id: commentId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload(); // 刷新页面以更新评论状态
                } else {
                    alert('错误: ' + response.data);
                }
            },
            error: function() {
                alert('请求失败，请重试。');
            }
        });
    });
});
