<?php
/*
Plugin Name: WP Private Comments
Plugin URI: https://hjyl.org/wp-private-comments
Description: 允许用户提交私密评论，管理员可以在后台查看和改变其状态。
Version: 1.0
Requires at least: 6.0
Requires PHP: 8.0
Author: HJYL
Author URI: https://hjyl.org/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: hjyl
*/

/*
此插件是参考AI生成的代码，和料网分享的私密评论功能代码，结合自身的理解，加以修改而成。

因此插件源码秉着GPLv3 or later的精神而分享。
*/

// 添加私密评论选项
// function pct_add_private_comment_field($fields) {
//     $fields['private_comment'] = '<p class="comment-form-private-comment">
//         <input type="checkbox" id="private_comment" name="private_comment" value="1" />
//         <label for="private_comment">私密评论</label>
//     </p>';
//     return $fields;
// }
// add_filter('comment_form_default_fields', 'pct_add_private_comment_field');

function add_private_comment_checkbox($button) {
    $fields = '<input type="checkbox" id="private_comment" name="private_comment" value="1" /> <label for="private_comment">私密评论</label>';
    return $button . ' ' . $fields;
}
add_filter('comment_form_submit_button', 'add_private_comment_checkbox');

// 保存私密评论状态
function pct_save_private_comment($comment_id) {
    if (isset($_POST['private_comment'])) {
        add_comment_meta($comment_id, 'private_comment', 1, true);
    }
}
add_action('comment_post', 'pct_save_private_comment');

// 仅管理员可以查看私密评论
// function pct_filter_comments($comments) {
//     if (!current_user_can('administrator')) {
//         foreach ($comments as $key => $comment) {
//             if (get_comment_meta($comment->comment_ID, 'private_comment', true)) {
//                 unset($comments[$key]);
//             }
//         }
//     }
//     return $comments;
// }
// add_filter('comments_array', 'pct_filter_comments');

// 前端评论显示
function liao_private_message_hook( $comment_content , $comment){
    $comment_ID = $comment->comment_ID;
    $parent_ID = $comment->comment_parent;
    $parent_email = get_comment_author_email($parent_ID);
    $is_private = get_comment_meta($comment_ID, 'private_comment', true);
    $email = $comment->comment_author_email;
    $current_commenter = wp_get_current_commenter();
    if ( $is_private ) $comment_content = '<span style="color:#1c73e0">#私密#</span> ' . $comment_content;
    if ( $current_commenter['comment_author_email'] == $email || $parent_email == $current_commenter['comment_author_email'] || current_user_can('delete_user') ) return $comment_content;
    if ( $is_private ) return '<span style="color:#1c73e0"><svg
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="15"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="feather feather-lock"
        >
            <rect x="3" y="11" width="18" height="12" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>此评论为私密评论。</span>';
    return $comment_content;
}
add_filter('get_comment_text','liao_private_message_hook',10,2);

// 在评论管理页面显示私密评论状态
function pct_add_private_comment_column($columns) {
    $columns['private_comment'] = '私密评论';
    return $columns;
}
add_filter('manage_edit-comments_columns', 'pct_add_private_comment_column');

function pct_show_private_comment_column($column, $comment_id) {
    if ($column === 'private_comment') {
        $is_private = get_comment_meta($comment_id, 'private_comment', true);
        //echo $is_private ? '是' : '否';
        if (current_user_can('administrator')) {
            $toggle_text = $is_private ? '私密' : '公开';
            echo ' <a href="#" class="pct-toggle-private" data-comment-id="' . $comment_id . '">' . $toggle_text . '</a>';
        }
    }
}
add_action('manage_comments_custom_column', 'pct_show_private_comment_column', 10, 2);

// AJAX 处理函数
function pct_toggle_private_comment() {
    if (!current_user_can('administrator')) {
        wp_send_json_error('没有权限');
    }

    $comment_id = intval($_POST['comment_id']);
    $is_private = get_comment_meta($comment_id, 'private_comment', true);

    if ($is_private) {
        delete_comment_meta($comment_id, 'private_comment');
        wp_send_json_success('评论已公开');
    } else {
        add_comment_meta($comment_id, 'private_comment', 1, true);
        wp_send_json_success('评论已设为私密');
    }
}
add_action('wp_ajax_pct_toggle_private_comment', 'pct_toggle_private_comment');

// 添加 JavaScript 代码
function pct_enqueue_scripts() {
    wp_enqueue_script('pct-ajax-script', plugin_dir_url(__FILE__) . 'hjyl-private-comments.js', array('jquery'), null, true);
    wp_localize_script('pct-ajax-script', 'pct_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('admin_enqueue_scripts', 'pct_enqueue_scripts');
