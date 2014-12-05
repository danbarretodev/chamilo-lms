<?php
/* For licensing terms, see /license.txt */
/**
 * Sessions edition script
 * @package chamilo.admin
 */
/**
 * Code
 */

// name of the language file that needs to be included
$language_file ='admin';
$cidReset = true;
require_once '../inc/global.inc.php';

$advancedSessionsPluginFilePath = api_get_path(PLUGIN_PATH) . 'advancedsessions/src/AdvancedSessionsPlugin.class.php';

if (file_exists($advancedSessionsPluginFilePath)) {
    require_once api_get_path(PLUGIN_PATH) . 'advancedsessions/src/AdvancedSessionsPlugin.class.php';
}

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

$formSent = 0;

// Database Table Definitions
$tbl_user		= Database::get_main_table(TABLE_MAIN_USER);
$tbl_session	= Database::get_main_table(TABLE_MAIN_SESSION);

$id = intval($_GET['id']);

SessionManager::protect_session_edit($id);
$infos = SessionManager::fetch($id);

$id_coach = $infos['id_coach'];
$tool_name = get_lang('EditSession');

$interbreadcrumb[] = array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array('url' => "session_list.php","name" => get_lang('SessionList'));
$interbreadcrumb[] = array('url' => "resume_session.php?id_session=".$id,"name" => get_lang('SessionOverview'));

list($year_start, $month_start, $day_start) = explode('-', $infos['date_start']);
list($year_end, $month_end, $day_end) = explode('-', $infos['date_end']);

// Default value
$showDescriptionChecked = 'checked';

if (isset($infos['show_description'])) {
    if (!empty($infos['show_description'])) {
        $showDescriptionChecked = 'checked';
    } else {
        $showDescriptionChecked = null;
    }
}

$end_year_disabled = $end_month_disabled = $end_day_disabled = '';

if (isset($_POST['formSent']) && $_POST['formSent']) {
	$formSent = 1;
}

$order_clause = 'ORDER BY ';
$order_clause .= api_sort_by_first_name() ? 'firstname, lastname, username' : 'lastname, firstname, username';

$sql="SELECT user_id,lastname,firstname,username FROM $tbl_user WHERE status='1'".$order_clause;

if (api_is_multiple_url_enabled()) {
	$table_access_url_rel_user= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
	$access_url_id = api_get_current_access_url_id();
	if ($access_url_id != -1) {
		$sql="SELECT DISTINCT u.user_id,lastname,firstname,username FROM $tbl_user u INNER JOIN $table_access_url_rel_user url_rel_user ON (url_rel_user.user_id = u.user_id)
			  WHERE status='1' AND access_url_id = '$access_url_id' $order_clause";
	}
}

$result     = Database::query($sql);
$Coaches    = Database::store_result($result);
$thisYear   = date('Y');

$daysOption = array();

for ($i = 1; $i <= 31; $i++) {
    $day = sprintf("%02d", $i);
    $daysOption[$day] = $day;
}

$monthsOption = array();

for ($i = 1; $i <= 12; $i++) {
    $month = sprintf("%02d", $i);
    
    $monthsOption[$month] = $month;
}

$yearsOption = array();

for ($i = $thisYear - 5; $i <= ($thisYear + 5); $i++) {
    $yearsOption[$i] = $i;
}

$coachesOption = array(
    '' => '----- ' . get_lang('None') . ' -----'
);

foreach ($Coaches as $coach) {
    $personName = api_get_person_name($coach['firstname'], $coach['lastname']);

    $coachesOption[$coach['user_id']] = "$personName ({$coach['username']})";
}

$Categories = SessionManager::get_all_session_category();

$categoriesOption = array(
    '0' => get_lang('None')
);

if ($Categories != false) {
    foreach ($categoriesList as $categoryItem) {
        $categoriesOption[$categoryItem['id']] = $categoryItem['name'];
    }
}

$formAction = api_get_self() . '?';
$formAction .= http_build_query(array(
    'page' => Security::remove_XSS($_GET['page']),
    'id' => $id
));

$form = new FormValidator('edit_session', 'post', $formAction);

$form->addElement('header', $tool_name);

$form->addElement('text', 'name', get_lang('SessionName'), array(
    'class' => 'span4',
    'maxlength' => 50,
    'value' => $formSent ? api_htmlentities($name,ENT_QUOTES,$charset) : ''
));
$form->addRule('name', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('name', get_lang('SessionNameAlreadyExists'), 'callback', 'check_session_name');

$form->addElement('select', 'id_coach', get_lang('CoachName'), $coachesOption, array(
    'id' => 'coach_username',
    'class' => 'chzn-select',
    'style' => 'width:370px;',
    'title' => get_lang('Choose')
));
$form->addRule('id_coach', get_lang('ThisFieldIsRequired'), 'required');

$form->add_select('session_category', get_lang('SessionCategory'), $categoriesOption, array(
    'id' => 'session_category',
    'class' => 'chzn-select',
    'style' => 'width:370px;'
));

$form->addElement('advanced_settings','<a class="btn-show" id="show-options" href="#">'.get_lang('DefineSessionOptions').'</a>');

if ($infos['nb_days_access_before_beginning'] != 0 || $infos['nb_days_access_after_end'] != 0) {
    $form->addElement('html','<div id="options" style="display:block;">');
} else {
    $form->addElement('html','<div id="options" style="display:none;">');
}

$form->addElement('text', 'nb_days_access_before', arra('', '', get_lang('DaysBefore')), array(
    'style' => 'width: 30px;'
));

$form->addElement('text', 'nb_days_access_after', array('', '', get_lang('DaysAfter')), array(
    'style' => 'width: 30px;'
));

$form->addElement('html','</div>');

if ($year_start!="0000") {
    $form->addElement('checkbox', 'start_limit', '', get_lang('DateStartSession'), array(
        'onchange' => 'disable_starttime(this)',
        'id' => 'start_limit',
        'checked' => ''
    ));

    $form->addElement('html','<div id="start_date" style="display:block">');
} else {
    $form->addElement('checkbox', 'start_limit', '', get_lang('DateStartSession'), array(
        'onchange' => 'disable_starttime(this)',
        'id' => 'start_limit'
    ));

    $form->addElement('html','<div id="start_date" style="display:none">');
}

$startDateGroup = array ();
$startDateGroup[] = $form->createElement('select', 'day_start', null, $daysOption);
$startDateGroup[] = $form->createElement('select', 'month_start', null, $monthsOption);
$startDateGroup[] = $form->createElement('select', 'year_start', null, $yearsOption);

$form->addGroup($startDateGroup, 'start_date_group', null, ' / ', true);

$form->addElement('html','</div>');

if ($year_end != "0000") {
    $form->addElement('checkbox', 'end_limit', '', get_lang('DateEndSession'), array(
        'onchange' => 'disable_endtime(this)',
        'id' => 'end_limit',
        'checked' => ''
    ));

    $form->addElement('html','<div id="end_date" style="display:block">');
} else {
    $form->addElement('checkbox', 'end_limit', '', get_lang('DateEndSession'), array(
        'onchange' => 'disable_endtime(this)',
        'id' => 'end_limit'
    ));

    $form->addElement('html','<div id="end_date" style="display:none">');
}

$endDateGroup = array();
$endDateGroup[] = $form->createElement('select', 'day_end', null, $daysOption);
$endDateGroup[] = $form->createElement('select', 'month_end', null, $monthsOption);
$endDateGroup[] = $form->createElement('select', 'year_end', null, $yearsOption);

$form->addGroup($endDateGroup, 'end_date_group', null, ' / ', true);

$visibilityGroup = array();
$visibilityGroup[] = $form->createElement('advanced_settings', get_lang('SessionVisibility'));
$visibilityGroup[] = $form->createElement('select', 'session_visibility', null, array(
    SESSION_VISIBLE_READ_ONLY => get_lang('SessionReadOnly'),
    SESSION_VISIBLE => get_lang('SessionAccessible'),
    SESSION_INVISIBLE => api_ucfirst(get_lang('SessionNotAccessible'))
), array(
    'style' => 'width:250px;'
));

$form->addGroup($visibilityGroup, 'visibility_group', null, null, false);

$form->addElement('html','</div>');

if (array_key_exists('show_description', $infos)) {
    $form->addElement('textarea', 'description', get_lang('Description'));

    $chkDescriptionAttributes = array();

    if (!empty($showDescriptionChecked)) {
        $chkDescriptionAttributes['checked'] = '';
    }

    $form->addElement('checkbox', 'show_description', null, get_lang('ShowDescription'), $chkDescriptionAttributes);
}

if (SessionManager::durationPerUserIsEnabled()) {
    $duration = empty($infos['duration']) ? null : $infos['duration'];

    $form->addElement('text', 'duration', get_lang('SessionDurationTitle'), array(
        'class' => 'span1',
        'maxlength' => 50
    ));
    $form->addElement('advanced_settings', get_lang('SessionDurationDescription'));
}

//Extra fields
$extra_field = new ExtraField('session');
$extra = $extra_field->addElements($form, $id);

$htmlHeadXtra[] ='
<script>

$(function() {
    '.$extra['jquery_ready_content'].'
});
</script>';

$form->addElement('button', 'submit', get_lang('ModifyThisSession'), array(
    'class' => 'save'
));

$formDefaults = array(
    'id_coach' => $infos['id_coach'],
    'session_category' => $infos['session_category_id'],
    'start_date_group[day_start]' => $day_start,
    'start_date_group[month_start]' => $month_start,
    'start_date_group[year_start]' => $year_start,
    'end_date_group[day_end]' => $day_end,
    'end_date_group[month_end]' => $month_end,
    'end_date_group[year_end]' => $year_end,
    'session_visibility' => $infos['visibility'],
    'description' => array_key_exists('show_description', $infos) ? $infos['description'] : ''
);

if ($formSent) {
    $formDefaults['name'] = api_htmlentities($name,ENT_QUOTES,$charset);
    $formDefaults['nb_days_access_before'] = api_htmlentities($nb_days_access_before,ENT_QUOTES,$charset);
    $formDefaults['nb_days_access_after'] = api_htmlentities($nb_days_access_after,ENT_QUOTES,$charset);
} else {
    $formDefaults['name'] = api_htmlentities($infos['name'],ENT_QUOTES,$charset);
    $formDefaults['nb_days_access_before'] = api_htmlentities($infos['nb_days_access_before_beginning'],ENT_QUOTES,$charset);
    $formDefaults['nb_days_access_after'] = api_htmlentities($infos['nb_days_access_after_end'],ENT_QUOTES,$charset);
}

if (SessionManager::durationPerUserIsEnabled()) {
    if ($formSent) {
        $formDefaults['duration'] = Security::remove_XSS($duration);
    } else {
        $formDefaults['duration'] = $duration;
    }
}

$form->setDefaults($formDefaults);

if ($form->validate()) {
    $params = $form->getSubmitValues();

    $name = $params['name'];
    $year_start = $params['start_date_group']['year_start'];
    $month_start = $params['start_date_group']['month_start'];
    $day_start = $params['start_date_group']['day_start'];
    $year_end = $params['end_date_group']['year_end'];
    $month_end = $params['end_date_group']['month_end'];
    $day_end = $params['end_date_group']['day_end'];
    $nb_days_acess_before = $params['nb_days_access_before'];
    $nb_days_acess_after = $params['nb_days_acc ess_after'];
    $id_coach = $params['id_coach'];
    $id_session_category = $params['session_category'];
    $id_visibility = $params['session_visibility'];
    $duration = isset($params['duration']) ? $params['duration'] : null;
    $description = isset($params['description']) ? $params['description'] : null;
    $showDescription = isset($params['show_description']) ? 1: 0;

    $end_limit = $params['end_limit'];
    $start_limit = $params['start_limit'];

    if (empty($end_limit) && empty($start_limit)) {
        $nolimit = 1;
    } else {
        $nolimit = null;
    }

    $extraFields = array();

    foreach ($params as $key => $value) {
        if (strpos($key, 'extra_') === 0) {
            $extraFields[$key] = $value;
        }
    }

    $return = SessionManager::edit_session(
        $id,
        $name,
        $year_start,
        $month_start,
        $day_start,
        $year_end,
        $month_end,
        $day_end,
        $nb_days_acess_before,
        $nb_days_acess_after,
        $nolimit,
        $id_coach,
        $id_session_category,
        $id_visibility,
        $start_limit,
        $end_limit,
        $description,
        $showDescription,
        $duration,
        $extraFields
    );

	if ($return == strval(intval($return))) {
        if (class_exists('AdvancedSessionsPlugin') && AdvancedSessionsPlugin::hasDescriptionField()) {
            AdvancedSessionsPlugin::saveSessionFieldValue($return, $_POST['description']);
        }

		header('Location: resume_session.php?id_session='.$return);
		exit();
	}
}

// display the header
Display::display_header($tool_name);

if (!empty($return)) {
    Display::display_error_message($return,false);
}
?>

<form class="form-horizontal" method="post" name="form" action="<?php echo api_get_self(); ?>?page=<?php echo Security::remove_XSS($_GET['page']) ?>&id=<?php echo $id; ?>" style="margin:0px;">
<fieldset>
    <legend><?php echo $tool_name; ?></legend>
    <input type="hidden" name="formSent" value="1">

    <div class="control-group">
        <label class="control-label">
            <?php echo get_lang('SessionName') ?>
        </label>
        <div class="controls">
            <input type="text" name="name" class="span4" maxlength="50" value="<?php if($formSent) echo api_htmlentities($name,ENT_QUOTES,$charset); else echo api_htmlentities($infos['name'],ENT_QUOTES,$charset); ?>">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">
            <?php echo get_lang('CoachName') ?>
        </label>
        <div class="controls">
            <select class="chzn-select" name="id_coach" style="width:380px;" title="<?php echo get_lang('Choose'); ?>" >
                <option value="">----- <?php echo get_lang('None') ?> -----</option>
                <?php foreach($Coaches as $enreg) { ?>
                <option value="<?php echo $enreg['user_id']; ?>" <?php if(($enreg['user_id'] == $infos['id_coach']) || ($enreg['user_id'] == $id_coach)) echo 'selected="selected"'; ?>><?php echo api_get_person_name($enreg['firstname'], $enreg['lastname']).' ('.$enreg['username'].')'; ?></option>
                <?php
                }
                unset($Coaches);
                $Categories = SessionManager::get_all_session_category();
            ?>
        </select>
        </div>
    </div>
    <?php if (class_exists('AdvancedSessionsPlugin') && AdvancedSessionsPlugin::hasDescriptionField()) { ?>
        <div class="control-group">
            <label class="control-label" for="description"><?php echo get_lang('Description') ?></label>
            <div class="controls">
                <?php $fckEditor = new FCKeditor('description'); ?>
                <?php $fckEditor->ToolbarSet = 'TrainingDescription'; ?>
                <?php $fckEditor->Value = AdvancedSessionsPlugin::getSessionDescription($id) ; ?>
                <?php echo $fckEditor->CreateHtml(); ?>
            </div>
        </div>
    <?php } ?>
    <div class="control-group">
        <label class="control-label">
            <?php echo get_lang('SessionCategory') ?>
        </label>
        <div class="controls">
                <select class="chzn-select" id="session_category" name="session_category" style="width:380px;" title="<?php echo get_lang('Select'); ?>">
        <option value="0"><?php get_lang('None'); ?></option>
        <?php
          if (!empty($Categories)) {
              foreach($Categories as $Rows)  { ?>
                <option value="<?php echo $Rows['id']; ?>" <?php if($Rows['id'] == $infos['session_category_id']) echo 'selected="selected"'; ?>><?php echo $Rows['name']; ?></option>
        <?php }
          }
         ?>
    </select>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <a href="javascript://" onclick="if(document.getElementById('options').style.display == 'none'){document.getElementById('options').style.display = 'block';}else{document.getElementById('options').style.display = 'none';}"><?php echo get_lang('DefineSessionOptions') ?></a>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <div style="display:
            <?php
                if($formSent){
                    if($nb_days_access_before!=0 || $nb_days_access_after!=0)
                        echo 'block';
                    else echo 'none';
                }
                else{
                    if($infos['nb_days_access_before_beginning']!=0 || $infos['nb_days_access_after_end']!=0)
                        echo 'block';
                    else
                        echo 'none';
                }
            ?>
                ;" id="options">

            <input type="text" name="nb_days_access_before" value="<?php if($formSent) echo api_htmlentities($nb_days_access_before,ENT_QUOTES,$charset); else echo api_htmlentities($infos['nb_days_access_before_beginning'],ENT_QUOTES,$charset); ?>" style="width: 30px;">&nbsp;<?php echo get_lang('DaysBefore') ?>
            <br />
            <br />
            <input type="text" name="nb_days_access_after" value="<?php if($formSent) echo api_htmlentities($nb_days_access_after,ENT_QUOTES,$charset); else echo api_htmlentities($infos['nb_days_access_after_end'],ENT_QUOTES,$charset); ?>" style="width: 30px;">&nbsp;<?php echo get_lang('DaysAfter') ?>

            </div>
        </div>
    </div>

    <div class="clear"></div>
    <div class="control-group">
        <div class="controls">
            <label for="start_limit">
                <input id="start_limit" type="checkbox" name="start_limit" onchange="disable_starttime(this)" <?php if ($year_start!="0000") echo "checked"; ?>/>
            <?php echo get_lang('DateStartSession');?>
            </label>
            <div id="start_date" style="<?php echo ($year_start=="0000") ? "display:none" : "display:block" ; ?>">
            <br />
              <select name="day_start">
                <option value="1">01</option>
                <option value="2" <?php if($day_start == 2) echo 'selected="selected"'; ?> >02</option>
                <option value="3" <?php if($day_start == 3) echo 'selected="selected"'; ?> >03</option>
                <option value="4" <?php if($day_start == 4) echo 'selected="selected"'; ?> >04</option>
                <option value="5" <?php if($day_start == 5) echo 'selected="selected"'; ?> >05</option>
                <option value="6" <?php if($day_start == 6) echo 'selected="selected"'; ?> >06</option>
                <option value="7" <?php if($day_start == 7) echo 'selected="selected"'; ?> >07</option>
                <option value="8" <?php if($day_start == 8) echo 'selected="selected"'; ?> >08</option>
                <option value="9" <?php if($day_start == 9) echo 'selected="selected"'; ?> >09</option>
                <option value="10" <?php if($day_start == 10) echo 'selected="selected"'; ?> >10</option>
                <option value="11" <?php if($day_start == 11) echo 'selected="selected"'; ?> >11</option>
                <option value="12" <?php if($day_start == 12) echo 'selected="selected"'; ?> >12</option>
                <option value="13" <?php if($day_start == 13) echo 'selected="selected"'; ?> >13</option>
                <option value="14" <?php if($day_start == 14) echo 'selected="selected"'; ?> >14</option>
                <option value="15" <?php if($day_start == 15) echo 'selected="selected"'; ?> >15</option>
                <option value="16" <?php if($day_start == 16) echo 'selected="selected"'; ?> >16</option>
                <option value="17" <?php if($day_start == 17) echo 'selected="selected"'; ?> >17</option>
                <option value="18" <?php if($day_start == 18) echo 'selected="selected"'; ?> >18</option>
                <option value="19" <?php if($day_start == 19) echo 'selected="selected"'; ?> >19</option>
                <option value="20" <?php if($day_start == 20) echo 'selected="selected"'; ?> >20</option>
                <option value="21" <?php if($day_start == 21) echo 'selected="selected"'; ?> >21</option>
                <option value="22" <?php if($day_start == 22) echo 'selected="selected"'; ?> >22</option>
                <option value="23" <?php if($day_start == 23) echo 'selected="selected"'; ?> >23</option>
                <option value="24" <?php if($day_start == 24) echo 'selected="selected"'; ?> >24</option>
                <option value="25" <?php if($day_start == 25) echo 'selected="selected"'; ?> >25</option>
                <option value="26" <?php if($day_start == 26) echo 'selected="selected"'; ?> >26</option>
                <option value="27" <?php if($day_start == 27) echo 'selected="selected"'; ?> >27</option>
                <option value="28" <?php if($day_start == 28) echo 'selected="selected"'; ?> >28</option>
                <option value="29" <?php if($day_start == 29) echo 'selected="selected"'; ?> >29</option>
                <option value="30" <?php if($day_start == 30) echo 'selected="selected"'; ?> >30</option>
                <option value="31" <?php if($day_start == 31) echo 'selected="selected"'; ?> >31</option>
              </select>
              /
              <select name="month_start">
                <option value="1">01</option>
                <option value="2" <?php if($month_start == 2) echo 'selected="selected"'; ?> >02</option>
                <option value="3" <?php if($month_start == 3) echo 'selected="selected"'; ?> >03</option>
                <option value="4" <?php if($month_start == 4) echo 'selected="selected"'; ?> >04</option>
                <option value="5" <?php if($month_start == 5) echo 'selected="selected"'; ?> >05</option>
                <option value="6" <?php if($month_start == 6) echo 'selected="selected"'; ?> >06</option>
                <option value="7" <?php if($month_start == 7) echo 'selected="selected"'; ?> >07</option>
                <option value="8" <?php if($month_start == 8) echo 'selected="selected"'; ?> >08</option>
                <option value="9" <?php if($month_start == 9) echo 'selected="selected"'; ?> >09</option>
                <option value="10" <?php if($month_start == 10) echo 'selected="selected"'; ?> >10</option>
                <option value="11" <?php if($month_start == 11) echo 'selected="selected"'; ?> >11</option>
                <option value="12" <?php if($month_start == 12) echo 'selected="selected"'; ?> >12</option>
              </select>
              /
              <select name="year_start">

            <?php
            for($i=$thisYear-5;$i <= ($thisYear+5);$i++) { ?>
                <option value="<?php echo $i; ?>" <?php if($year_start == $i) echo 'selected="selected"'; ?> ><?php echo $i; ?></option>
            <?php
            }
            ?>
              </select>
          </div>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <label for="end_limit">
                <input id="end_limit" type="checkbox" name="end_limit" onchange="disable_endtime(this)" <?php if ($year_end!="0000") echo "checked"; ?>/>
            <?php echo get_lang('DateEndSession') ?>
            </label>
          <div id="end_date" style="<?php echo ($year_end=="0000") ? "display:none" : "display:block" ; ?>">
          <br />

          <select name="day_end" <?php echo $end_day_disabled; ?> >
        	<option value="1">01</option>
        	<option value="2" <?php if($day_end == 2) echo 'selected="selected"'; ?> >02</option>
        	<option value="3" <?php if($day_end == 3) echo 'selected="selected"'; ?> >03</option>
        	<option value="4" <?php if($day_end == 4) echo 'selected="selected"'; ?> >04</option>
        	<option value="5" <?php if($day_end == 5) echo 'selected="selected"'; ?> >05</option>
        	<option value="6" <?php if($day_end == 6) echo 'selected="selected"'; ?> >06</option>
        	<option value="7" <?php if($day_end == 7) echo 'selected="selected"'; ?> >07</option>
        	<option value="8" <?php if($day_end == 8) echo 'selected="selected"'; ?> >08</option>
        	<option value="9" <?php if($day_end == 9) echo 'selected="selected"'; ?> >09</option>
        	<option value="10" <?php if($day_end == 10) echo 'selected="selected"'; ?> >10</option>
        	<option value="11" <?php if($day_end == 11) echo 'selected="selected"'; ?> >11</option>
        	<option value="12" <?php if($day_end == 12) echo 'selected="selected"'; ?> >12</option>
        	<option value="13" <?php if($day_end == 13) echo 'selected="selected"'; ?> >13</option>
        	<option value="14" <?php if($day_end == 14) echo 'selected="selected"'; ?> >14</option>
        	<option value="15" <?php if($day_end == 15) echo 'selected="selected"'; ?> >15</option>
        	<option value="16" <?php if($day_end == 16) echo 'selected="selected"'; ?> >16</option>
        	<option value="17" <?php if($day_end == 17) echo 'selected="selected"'; ?> >17</option>
        	<option value="18" <?php if($day_end == 18) echo 'selected="selected"'; ?> >18</option>
        	<option value="19" <?php if($day_end == 19) echo 'selected="selected"'; ?> >19</option>
        	<option value="20" <?php if($day_end == 20) echo 'selected="selected"'; ?> >20</option>
        	<option value="21" <?php if($day_end == 21) echo 'selected="selected"'; ?> >21</option>
        	<option value="22" <?php if($day_end == 22) echo 'selected="selected"'; ?> >22</option>
        	<option value="23" <?php if($day_end == 23) echo 'selected="selected"'; ?> >23</option>
        	<option value="24" <?php if($day_end == 24) echo 'selected="selected"'; ?> >24</option>
        	<option value="25" <?php if($day_end == 25) echo 'selected="selected"'; ?> >25</option>
        	<option value="26" <?php if($day_end == 26) echo 'selected="selected"'; ?> >26</option>
        	<option value="27" <?php if($day_end == 27) echo 'selected="selected"'; ?> >27</option>
        	<option value="28" <?php if($day_end == 28) echo 'selected="selected"'; ?> >28</option>
        	<option value="29" <?php if($day_end == 29) echo 'selected="selected"'; ?> >29</option>
        	<option value="30" <?php if($day_end == 30) echo 'selected="selected"'; ?> >30</option>
        	<option value="31" <?php if($day_end == 31) echo 'selected="selected"'; ?> >31</option>
          </select>
          /
          <select name="month_end" <?php echo $end_month_disabled; ?> >
        	<option value="1">01</option>
        	<option value="2" <?php if($month_end == 2) echo 'selected="selected"'; ?> >02</option>
        	<option value="3" <?php if($month_end == 3) echo 'selected="selected"'; ?> >03</option>
        	<option value="4" <?php if($month_end == 4) echo 'selected="selected"'; ?> >04</option>
        	<option value="5" <?php if($month_end == 5) echo 'selected="selected"'; ?> >05</option>
        	<option value="6" <?php if($month_end == 6) echo 'selected="selected"'; ?> >06</option>
        	<option value="7" <?php if($month_end == 7) echo 'selected="selected"'; ?> >07</option>
        	<option value="8" <?php if($month_end == 8) echo 'selected="selected"'; ?> >08</option>
        	<option value="9" <?php if($month_end == 9) echo 'selected="selected"'; ?> >09</option>
        	<option value="10" <?php if($month_end == 10) echo 'selected="selected"'; ?> >10</option>
        	<option value="11" <?php if($month_end == 11) echo 'selected="selected"'; ?> >11</option>
        	<option value="12" <?php if($month_end == 12) echo 'selected="selected"'; ?> >12</option>
          </select>
          /
          <select name="year_end" <?php echo $end_year_disabled; ?>>

        <?php
        for($i=$thisYear-5;$i <= ($thisYear+5);$i++) {
        ?>
        	<option value="<?php echo $i; ?>" <?php if($year_end == $i) echo 'selected="selected"'; ?> ><?php echo $i; ?></option>
        <?php
        }
        ?>
          </select>
           <br />      <br />

            <?php echo get_lang('SessionVisibility') ?> <br />
            <select name="session_visibility" style="width:250px;">
                <?php
                $visibility_list = array(
                    SESSION_VISIBLE_READ_ONLY => get_lang('SessionReadOnly'),
                    SESSION_VISIBLE => get_lang('SessionAccessible'),
                    SESSION_INVISIBLE => api_ucfirst(get_lang('SessionNotAccessible'))
                );
                foreach($visibility_list as $key=>$item): ?>
                <option value="<?php echo $key; ?>" <?php if($key == $infos['visibility']) echo 'selected="selected"'; ?>><?php echo $item; ?></option>
                <?php endforeach; ?>
            </select>
    </div>
    </div>
  </div>

    <?php if (array_key_exists('show_description', $infos)) { ?>

        <div class="control-group">
            <div class="controls">
                <?php echo get_lang('Description') ?> <br />
                <textarea name="description"><?php  echo $infos['description']; ?></textarea>
            </div>
        </div>

        <div class="control-group">
            <div class="controls">
                <label>
                <input id="show_description" type="checkbox" name="show_description" <?php echo $showDescriptionChecked ?> />
                <?php echo get_lang('ShowDescription') ?>
                </label>
            </div>
        </div>

    <?php } ?>

    <?php
        if (SessionManager::durationPerUserIsEnabled()) {
            if (empty($infos['duration'])) {
                $duration = null;
            } else {
                $duration = $infos['duration'];
            }
            ?>
            <div class="control-group">
                <label class="control-label">
                    <?php echo get_lang('SessionDurationTitle') ?> <br />
                </label>
                <div class="controls">
                    <input id="duration" type="text" name="duration" class="span1" maxlength="50" value="<?php if($formSent) echo Security::remove_XSS($duration); else echo $duration; ?>">
                    <br />
                    <?php echo get_lang('SessionDurationDescription') ?>
                </div>
            </div>

        <?php
        }
    ?>

$form->display();
?>

<script type="text/javascript">

<?php
//if($year_start=="0000") echo "setDisable(document.form.nolimit);\r\n";
?>

function setDisable(select) {

	document.form.day_start.disabled = (select.checked) ? true : false;
	document.form.month_start.disabled = (select.checked) ? true : false;
	document.form.year_start.disabled = (select.checked) ? true : false;

	document.form.day_end.disabled = (select.checked) ? true : false;
	document.form.month_end.disabled = (select.checked) ? true : false;
	document.form.year_end.disabled = (select.checked) ? true : false;

	document.form.session_visibility.disabled = (select.checked) ? true : false;
	document.form.session_visibility.selectedIndex = 0;

    document.form.start_limit.disabled = (select.checked) ? true : false;
    document.form.start_limit.checked = false;
    document.form.end_limit.disabled = (select.checked) ? true : false;
    document.form.end_limit.checked = false;

    var end_div = document.getElementById('end_date');
    end_div.style.display = 'none';

    var start_div = document.getElementById('start_date');
    start_div.style.display = 'none';
}

function disable_endtime(select) {
    var end_div = document.getElementById('end_date');
    if (end_div.style.display == 'none')
        end_div.style.display = 'block';
     else
        end_div.style.display = 'none';
    emptyDuration();
}

function disable_starttime(select) {
    var start_div = document.getElementById('start_date');
    if (start_div.style.display == 'none')
        start_div.style.display = 'block';
     else
        start_div.style.display = 'none';
    emptyDuration();
}

function emptyDuration() {
    if ($('#duration').val()) {
        $('#duration').val('');
    }
}

$(document).on('ready', function (){
    $('#show-options').on('click', function (e) {
        e.preventDefault();

        var display = $('#options').css('display');

        display === 'block' ? $('#options').slideUp() : $('#options').slideDown() ;
    });
});

</script>
<?php
Display::display_footer();
