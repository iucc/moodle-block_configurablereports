<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Email form added to enable email to selected users.
require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/group/lib.php');

require_login();
global $PAGE, $USER, $DB, $COURSE;
$context = context_course::instance($COURSE->id);
$PAGE->set_context($context);

class add_to_group_form extends moodleform {

    public function definition() {
        global $COURSE;

        $mform =& $this->_form;
        $context = \context_course::instance($COURSE->id);
        $editoroptions = [
            'trusttext' => true,
            'subdirs' => true,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'context' => $context
        ];

        $mform->addElement('hidden', 'usersids', $this->_customdata['usersids']);
        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);

        //$mform->addElement('text', 'newgroupname', get_string('group_newgroupname', 'block_configurable_reports'));
        //mform->setType('newgroupname', PARAM_TEXT);
        //$mform->addRule('newgroupname', null, 'required');

        // Prepare the list of groups.
        $groups = groups_get_all_groups($COURSE->id);

        if (empty($groups)) {
            // Generate an error.
            print_error('groupsnone', 'block_configurable_reports');
        }

        $groupchoices = array();
        foreach ($groups as $group) {
            $groupchoices[$group->id] = $group->name;
        }
        unset($groups);

        if (count($groupchoices) == 0) {
            $groupchoices[0] = get_string('none');
        }

        $mform->addElement('select', 'groupid', get_string('overridegroup', 'lesson'), $groupchoices);

        $buttons = array();
        $buttons[] =& $mform->createElement('submit', 'send', get_string('group_addtogroup', 'block_configurable_reports'));
        $buttons[] =& $mform->createElement('cancel');

        $mform->addGroup($buttons, 'buttons', get_string('actions'));
    }
}

$userids = optional_param('userids', null, PARAM_TEXT);
$courseid = optional_param('courseid', null, PARAM_INT);
$newgroupname = optional_param('newgroupname', null, PARAM_TEXT);

if (empty($newgroupname)) { // If no group name, ask for group, form.
    $form = new \add_to_group_form(null, ['usersids' => implode(',', $userids), 'courseid' => $courseid]);

    if ($form->is_cancelled()) {
        redirect(new \moodle_url('/course/view.php?id='.$data->courseid));
    } else if ($data = $form->get_data()) {
        foreach (explode(',', $data->usersids) as $userid) {
            $abouttosenduser = $DB->get_record('user', ['id' => $userid]);
            email_to_user($abouttosenduser, $USER, $data->subject, format_text($data->content['text']), $data->content['text']);
        }
        // After emails were sent... go back to where you came from.
        redirect(new \moodle_url('/course/view.php?id='.$data->courseid));
    }
} else {
    $newgroupdata = new stdClass();
    $newgroupdata->name = $newgroupname;
    $newgroupdata->courseid = $courseid;
    $newgroupdata->description = 'Created by configurable report id=XXX';
    $groupid = groups_create_group($newgroupdata);
    if ($groupid && !empty($userids)) {
        foreach ($userids as $userid) {
            //$abouttosenduser = $DB->get_record('user', ['id' => $userid]);
            groups_add_member($groupid, $userid);
        }
    } else {
        print_error('missinguserids', 'block_configurable_reports');
    }

}


$PAGE->set_title(get_string('group_addtogroup', 'block_configurable_reports'));
$PAGE->set_heading(format_string($COURSE->fullname));
$PAGE->navbar->add(get_string('group_addtogroup', 'block_configurable_reports'));
$reportid = optional_param('reportid', 1, PARAM_INT);
$PAGE->set_url('/blocks/configurable_reports/add_to_group.php', ['id' => $reportid]);

echo $OUTPUT->header();

echo \html_writer::start_tag('div', ['class' => 'no-overflow']);
if (empty($newgroupname)) {
    $form->display();
} else {
    echo 'Users added to group';
}

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();
