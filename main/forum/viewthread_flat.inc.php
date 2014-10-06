<?php
/* For licensing terms, see /license.txt */
/**
 * This script manages the display of forum threads in flat view
 * @copyright Julio Montoya <gugli100@gmail.com> UI Improvements + lots of bugfixes
 * @package chamilo.forum
 */
//delete attachment file
if ((isset($_GET['action']) && $_GET['action']=='delete_attach') && isset($_GET['id_attach'])) {
    delete_attachment(0,$_GET['id_attach']);
}
if (isset($current_thread['thread_id'])) {
    $rows = get_posts($current_thread['thread_id']);
    $increment = 0;
    $clean_forum_id  = intval($_GET['forum']);
    $clean_thread_id = intval($_GET['thread']);
    $origin = Security::remove_XSS($_REQUEST['origin']);
    $locked = api_resource_is_locked_by_gradebook($clean_thread_id, LINK_FORUM_THREAD);
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $postId = $row['post_id'];
            echo getPostPrototype(
                $clean_forum_id,
                $clean_thread_id,
                $row,
                $origin,
                $increment
            );
            $increment++;
        }
    }
}
