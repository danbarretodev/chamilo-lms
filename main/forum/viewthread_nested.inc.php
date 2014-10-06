<?php
/* For licensing terms, see /license.txt */

/**
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @author Julio Montoya <gugli100@gmail.com> UI Improvements + lots of bugfixes
 * @copyright Ghent University
 * @package chamilo.forum
 */

require_once api_get_path(SYS_CODE_PATH).'forum/forumfunction.inc.php';

// Are we in a lp ?
$origin = '';
if (isset($_GET['origin'])) {
    $origin =  Security::remove_XSS($_GET['origin']);
}

//delete attachment file
if ((isset($_GET['action']) && $_GET['action']=='delete_attach') && isset($_GET['id_attach'])) {
    delete_attachment(0,$_GET['id_attach']);
}

$rows = get_posts($_GET['thread']);
$rows = calculate_children($rows);
$count=0;
$clean_forum_id  = intval($_GET['forum']);
$clean_thread_id = intval($_GET['thread']);
$group_id = api_get_group_id();
$locked = api_resource_is_locked_by_gradebook($clean_thread_id, LINK_FORUM_THREAD);

foreach ($rows as $post) {
    echo Display::div(getPostPrototype(
        $clean_forum_id,
        $clean_thread_id,
        $post,
        $origin,
        $count
    ), array('style' => 'margin-left: ' . $post['indent_cnt'] * 20 . 'px;'));
    $count++;
}
