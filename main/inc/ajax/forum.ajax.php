<?php
/** For licensing terms, see /license.txt */
/**
 * Responses to AJAX calls for forum attachments
 * @package chamilo/forum
 * @author Daniel Barreto Alva <daniel.barreto@beeznest.com>
 */

/**
 * Init
 */
require_once '../global.inc.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';

/**
 * Main code
 */
// Create a default error response
$json = array(
    'error' => true,
    'errorMessage' => 'ERROR',
);
$action = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;
// Check if exist action
if (!empty($action)) {
    require_once api_get_path(SYS_CODE_PATH) . 'forum/forumfunction.inc.php';
    $current_forum = get_forum_information(intval($_GET['forum']));
    $current_forum_category = get_forumcategory_information($current_forum['forum_category']);
    switch($action) {
        case 'replymessage':
            // First, protect this script
            api_protect_course_script(false);
            if (!empty($_REQUEST['forum']) && !empty($_REQUEST['forum'])) {
                // The user is not allowed here if
                // 1. the forum category, forum or thread is invisible (visibility==0)
                // 2. the forum category, forum or thread is locked (locked <>0)
                // 3. if anonymous posts are not allowed
                // The only exception is the course manager
                // They are several pieces for clarity.
                if (!api_is_allowed_to_edit(null, true) AND (($current_forum_category && $current_forum_category['visibility'] == 0) OR $current_forum['visibility'] == 0)) {
                    $json['errorMessage'] = '1. the forum category, forum or thread is invisible (visibility==0)';
                    break;
                }
                if (!api_is_allowed_to_edit(null, true) AND (($current_forum_category && $current_forum_category['locked'] <> 0 ) OR $current_forum['locked'] <> 0 OR $current_thread['locked'] <> 0)) {
                    $json['errorMessage'] = '2. the forum category, forum or thread is locked (locked <>0)';
                    break;
                }
                if (api_is_anonymous() AND $current_forum['allow_anonymous'] == 0) {
                    $json['errorMessage'] = '3. if anonymous posts are not allowed';
                    break;
                }

                $check = Security::check_token('post');
                if ($check) {
                    if ($_REQUEST['thread_qualify_gradebook'] == '1' && empty($_REQUEST['weight_calification'])) {
                        $json['errorMessage'] = get_lang('YouMustAssignWeightOfQualification');
                        break;
                    }
                    Security::clear_token();
                    $result = store_reply($current_forum, $_REQUEST);
                    $json['result'] = print_r($result,1);
                }
            }
            break;

    }
}

/**
 * Display
 */
echo json_encode($json);
exit;