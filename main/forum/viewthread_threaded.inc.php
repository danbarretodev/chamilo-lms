<?php
/* For licensing terms, see /license.txt */

/**
 * These files are a complete rework of the forum. The database structure is
 * based on phpBB but all the code is rewritten. A lot of new functionalities
 * are added:
 * - forum categories and forums can be sorted up or down, locked or made invisible
 * - consistent and integrated forum administration
 * - forum options:     are students allowed to edit their post?
 *                      moderation of posts (approval)
 *                      reply only forums (students cannot create new threads)
 *                      multiple forums per group
 * - sticky messages
 * - new view option: nested view
 * - quoting a message
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @author Julio Montoya <gugli100@gmail.com> UI Improvements + lots of bugfixes
 *
 * @package chamilo.forum
 */

require_once api_get_path(SYS_CODE_PATH).'forum/forumfunction.inc.php';
$forumUrl = api_get_path(WEB_CODE_PATH).'forum/';

$rows = get_posts($_GET['thread']);
$rows = calculate_children($rows);

if (isset($_GET['post']) && $_GET['post']) {
    $display_post_id = intval($_GET['post']);
} else {
    // we need to display the first post
    reset($rows);
    $current = current($rows);
    $display_post_id = $current['post_id'];
}

// Are we in a lp ?
$origin = '';
if(isset($_GET['origin'])) {
    $origin =  Security::remove_XSS($_GET['origin']);
}

// Delete attachment file.
if ((isset($_GET['action']) && $_GET['action']=='delete_attach') && isset($_GET['id_attach'])) {
    delete_attachment(0, $_GET['id_attach']);
}

// 		Displaying the thread (structure)

$thread_structure="<div class=\"structure\">".get_lang('Structure')."</div>";
$counter=0;
$count=0;
$prev_next_array=array();

$clean_forum_id  = intval($_GET['forum']);
$clean_thread_id = intval($_GET['thread']);
$group_id = api_get_group_id();

foreach ($rows as $post) {
    $counter++;
    $indent=$post['indent_cnt']*'20';
    $thread_structure.= "<div style=\"margin-left: " .
        $indent . "px;\" data-post-id=\"". $post['post_id'] . "\" >";

    if (isset($whatsnew_post_info[$current_forum['forum_id']][$current_thread['thread_id']][$post['post_id']]) AND
        !empty($whatsnew_post_info[$current_forum['forum_id']][$current_thread['thread_id']][$post['post_id']]) AND
        !empty($whatsnew_post_info[$_GET['forum']][$post['thread_id']])
    ) {
        $post_image = Display::return_icon('forumpostnew.gif');
    } else {
        $post_image = Display::return_icon('forumpost.gif');
    }
    $thread_structure.= $post_image;
    if (isset($_GET['post']) &&
        $_GET['post'] == $post['post_id'] OR
        ($counter==1 AND !isset($_GET['post']))
    ) {
        $thread_structure .= '<strong>'.prepare4display($post['post_title']).'</strong></div>';
        $prev_next_array[]= $post['post_id'];
    } else {
        if ($post['visible']=='0') {
            $class=' class="invisible"';
        } else {
            $class='';
        }
        $count_loop=($count==0)?'&amp;id=1' : '';
        $thread_structure.= "<a href=\"viewthread.php?".api_get_cidreq()."&forum=".$clean_forum_id."&thread=".$clean_thread_id."&post=".$post['post_id']."&amp;origin=$origin$count_loop\" $class>".
            prepare4display($post['post_title'])."</a></div>";
        $prev_next_array[]=$post['post_id'];
    }
    $count++;
}

$locked = api_resource_is_locked_by_gradebook($clean_thread_id, LINK_FORUM_THREAD);

/* NAVIGATION CONTROLS */

$current_id = array_search($display_post_id, $prev_next_array);
$max = count($prev_next_array);
$next_id = $current_id + 1;
$prev_id = $current_id - 1;

// text
$first_message = get_lang('FirstMessage');
$last_message = get_lang('LastMessage');
$next_message = get_lang('NextMessage');
$prev_message = get_lang('PrevMessage');

// images
$first_img = Display::return_icon(
    'action_first.png',
    get_lang('FirstMessage'),
    array('style' => 'vertical-align: middle;')
);
$last_img = Display::return_icon(
    'action_last.png',
    get_lang('LastMessage'),
    array('style' => 'vertical-align: middle;')
);
$prev_img = Display::return_icon(
    'action_prev.png',
    get_lang('PrevMessage'),
    array('style' => 'vertical-align: middle;')
);
$next_img = Display::return_icon(
    'action_next.png',
    get_lang('NextMessage'),
    array('style' => 'vertical-align: middle;')
);

// links
$first_href = $forumUrl.'viewthread.php?'.api_get_cidreq().'&amp;forum='.$clean_forum_id.'&amp;thread='.$clean_thread_id.'&amp;gradebook='.$gradebook.'&id=1&amp;post='.$prev_next_array[0];
$last_href 	= $forumUrl.'viewthread.php?'.api_get_cidreq().'&amp;forum='.$clean_forum_id.'&amp;thread='.$clean_thread_id.'&amp;gradebook='.$gradebook.'&post='.$prev_next_array[$max-1];
$prev_href	= $forumUrl.'viewthread.php?'.api_get_cidreq().'&amp;forum='.$clean_forum_id.'&amp;thread='.$clean_thread_id.'&amp;gradebook='.$gradebook.'&post='.$prev_next_array[$prev_id];
$next_href	= $forumUrl.'viewthread.php?'.api_get_cidreq().'&amp;forum='.$clean_forum_id.'&amp;thread='.$clean_thread_id.'&amp;gradebook='.$gradebook.'&post='.$prev_next_array[$next_id];

echo '<center style="margin-top: 10px; margin-bottom: 10px;">';
//go to: first and previous
if ((int)$current_id > 0) {
    echo '<a href="'.$first_href.'" '.$class.' title='.$first_message.'>'.$first_img.' '.$first_message.'</a>';
    echo '<a href="'.$prev_href.'" '.$class_prev.' title='.$prev_message.'>'.$prev_img.' '.$prev_message.'</a>';
} else {
    echo '<b><span class="invisible">'.$first_img.' '.$first_message.'</b></span>';
    echo '<b><span class="invisible">'.$prev_img.' '.$prev_message.'</b></span>';
}

//  current counter
echo ' [ '.($current_id+1).' / '.$max.' ] ';

// go to: next and last
if (($current_id+1) < $max) {
    echo '<a href="'.$next_href.'" '.$class_next.' title='.$next_message.'>'.$next_message.' '.$next_img.'</a>';
    echo '<a href="'.$last_href.'" '.$class.' title='.$last_message.'>'.$last_message.' '.$last_img.'</a>';
} else {
    echo '<b><span class="invisible">'.$next_message.' '.$next_img.'</b></span>';
    echo '<b><span class="invisible">'.$last_message.' '.$last_img.'</b></span>';
}
echo '</center>';

// the style depends on the status of the message: approved or not
if ($rows[$display_post_id]['visible'] == '0') {
    $titleclass = 'forum_message_post_title_2_be_approved';
    $messageclass = 'forum_message_post_text_2_be_approved';
    $leftclass = 'forum_message_left_2_be_approved';
} else {
    $titleclass = 'forum_message_post_title';
    $messageclass = 'forum_message_post_text';
    $leftclass = 'forum_message_left';
}

// 		Displaying the message

// we mark the image we are displaying as set
unset($whatsnew_post_info[$current_forum['forum_id']][$current_thread['thread_id']][$rows[$display_post_id]['post_id']]);

echo getPostPrototype($clean_forum_id, $clean_thread_id, $rows[$display_post_id], $origin, $current_id);

echo $thread_structure;
