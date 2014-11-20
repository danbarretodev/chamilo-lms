<?php
/* For licensing terms, see /license.txt */
/*
 * Sessions reporting
 * @package chamilo.reporting
 */
ob_start();
// name of the language file that needs to be included
$language_file = array('registration', 'index', 'trad4all', 'tracking', 'admin');
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
api_block_anonymous_users();

$this_section = SECTION_TRACKING;

api_block_anonymous_users();
$htmlHeadXtra[] = api_get_jqgrid_js();
$interbreadcrumb[] = array ("url" => "index.php", "name" => get_lang('MySpace'));
Display::display_header(get_lang('Sessions'));

$export_csv = false;

if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $export_csv = true;
}

/*	MAIN CODE */

if (isset($_GET['id_coach']) && $_GET['id_coach'] != '') {
    $id_coach = intval($_GET['id_coach']);
} else {
    $id_coach = api_get_user_id();
}

$currentAction = REPORT_ACTION_SESSIONS;
if (api_is_drh() || api_is_session_admin() || api_is_platform_admin()) {

    $a_sessions = SessionManager::get_sessions_followed_by_drh(api_get_user_id());

    // Print action bar
    echo MySpace::getActionBar($currentAction, array());

    echo Display::page_header(get_lang('YourSessionsList'));

} else {
    $a_sessions = Tracking::get_sessions_coached_by_user($id_coach);
}

$form = new FormValidator('search_course', 'get', api_get_path(WEB_CODE_PATH).'mySpace/session.php');
$form->addElement('text', 'keyword', get_lang('Keyword'));
$form->addElement('button', 'submit', get_lang('Search'));
$form->addElement('hidden', 'session_id', $sessionId);
$keyword = null;
if ($form->validate()) {
    $keyword = $form->getSubmitValue('keyword');
}
$form->setDefaults(array('keyword' => $keyword));

$url = api_get_path(WEB_AJAX_PATH).'model.ajax.php?a=get_sessions_tracking&keyword='.Security::remove_XSS($keyword);

$columns = array(
    get_lang('Title'),
    get_lang('Date'),
    get_lang('NbCoursesPerSession'),
    get_lang('NbStudentPerSession'),
    get_lang('Details')
);

// Column config
$columnModel = array(
    array('name'=>'name', 'index'=>'name', 'width'=>'255', 'align'=>'left'),
    array('name'=>'date', 'index'=>'date', 'width'=>'150', 'align'=>'left','sortable'=>'false'),
    array('name'=>'course_per_session', 'index'=>'course_per_session', 'width'=>'150','sortable'=>'false'),
    array('name'=>'student_per_session', 'index'=>'student_per_session', 'width'=>'100','sortable'=>'false'),
    array('name'=>'details', 'index'=>'details', 'width'=>'100','sortable'=>'false')
);

$extraParams = array(
    'autowidth' => 'true',
    'height' => 'auto'
);

$js = '<script>
    $(function() {
        '.Display::grid_js(
        'session_tracking',
        $url,
        $columns,
        $columnModel,
        $extraParams,
        array(),
        null,
        true
    ).'
    });
</script>';

echo $js;
$form->display();

echo Display::grid_html('session_tracking');

Display::display_footer();
