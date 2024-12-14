<?php 

if( ! is_user_logged_in()){

    return;
}

if( ! current_user_can("administrator")){
    return;
}

$url = $_REQUEST['url'] ?? null;

$post_id = url_to_postid( $url );
if($post_id){
    $edit_link = get_edit_post_link( $post_id );
}

// if(is_tax()){
//     $term = get_queried_object();
//     $term_id = $term->term_id;
//     $edit_link = get_edit_term_link( $term_id );
// }


$admin_url = admin_url();


?>
<div class="app-toolbar fixed bottom-10 p-5 rounded-sm bg-slate-300">
    
    hi <?= wp_get_current_user()->display_name ?>:
    <a href="<?= $edit_link ?>" target="_blank" rel="noopener noreferrer">edit</a>, 
    <a href="<?= $admin_url ?>" rel="noopener noreferrer">admin</a>
</div>
