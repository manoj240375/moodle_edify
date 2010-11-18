<?php if (isloggedin()) {
    echo html_writer::link(new moodle_url('/login/logout.php', array('sesskey'=>sesskey())), get_string('logout'));
	echo html_writer::end_tag('li');
	echo html_writer::start_tag('li', array());
	echo html_writer::link(new moodle_url('/user/profile.php', array('id'=>$USER->id)), get_string('myprofile'));
	echo html_writer::end_tag('li');
	echo html_writer::start_tag('li', array());
	echo html_writer::link(new moodle_url('/my', array('id'=>$USER->id)), get_string('mycourses'));
	echo html_writer::end_tag('li');
	echo html_writer::start_tag('li', array());
	echo html_writer::link(new moodle_url('/blog', array('id'=>$USER->id)), get_string('myblog','theme_premiumseries'));
} else {
    echo html_writer::link(new moodle_url('/login/'), get_string('login'));
}
