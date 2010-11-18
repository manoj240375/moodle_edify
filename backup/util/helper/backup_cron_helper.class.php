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


/**
 * Utility helper for automated backups run through cron.
 *
 * @package    core
 * @subpackage backup
 * @copyright  2010 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class is an abstract class with methods that can be called to aid the
 * running of automated backups over cron.
 */
abstract class backup_cron_automated_helper {

    /** automated backups are active and ready to run */
    const STATE_OK = 0;
    /** automated backups are disabled and will not be run */
    const STATE_DISABLED = 1;
    /** automated backups are all ready running! */
    const STATE_RUNNING = 2;

    /** Course automated backup completed successfully */
    const BACKUP_STATUS_OK = 1;
    /** Course automated backup errored */
    const BACKUP_STATUS_ERROR = 0;
    /** Course automated backup never finished */
    const BACKUP_STATUS_UNFINISHED = 2;
    /** Course automated backup was skipped */
    const BACKUP_STATUS_SKIPPED = 3;


    /**
     * Runs the automated backups if required
     *
     * @global moodle_database $DB
     */
    public static function run_automated_backup() {
        global $CFG, $DB;

        $status = true;
        $emailpending = false;
        $now = time();

        mtrace("Checking automated backup status",'...');
        $state = backup_cron_automated_helper::get_automated_backup_state();
        if ($state === backup_cron_automated_helper::STATE_DISABLED) {
            mtrace('INACTIVE');
            return true;
        } else if ($state === backup_cron_automated_helper::STATE_RUNNING) {
            mtrace('RUNNING');
            mtrace("automated backup seems to be running. Execution delayed");
            return true;
        } else {
            mtrace('OK');
        }
        backup_cron_automated_helper::set_state_running();

        mtrace("Getting admin info");
        $admin = get_admin();
        if (!$admin) {
            mtrace("Error: No admin account was found");
            $state = false;
        }

        if ($status) {
            mtrace("Checking courses");
            mtrace("Skipping deleted courses", '...');
            mtrace(sprintf("%d courses", backup_cron_automated_helper::remove_deleted_courses_from_schedule()));
        }

        if ($status) {

            mtrace('Running required automated backups...');

            // This could take a while!
            @set_time_limit(0);
            raise_memory_limit(MEMORY_EXTRA);

            $nextstarttime = backup_cron_automated_helper::calculate_next_automated_backup($admin->timezone, $now);
            $showtime = "undefined";
            if ($nextstarttime > 0) {
                $showtime = userdate($nextstarttime,"",$admin->timezone);
            }

            $rs = $DB->get_recordset('course');
            foreach ($rs as $course) {
                $backupcourse = $DB->get_record('backup_courses', array('courseid'=>$course->id));
                if (!$backupcourse) {
                    $backupcourse = new stdClass;
                    $backupcourse->courseid = $course->id;
                    $DB->insert_record('backup_courses',$backupcourse);
                    $backupcourse = $DB->get_record('backup_courses', array('courseid'=>$course->id));
                }

                // Skip backup of unavailable courses that have remained unmodified in a month
                $skipped = false;
                if (empty($course->visible) && ($now - $course->timemodified) > 31*24*60*60) {  //Hidden + unmodified last month
                    $backupcourse->laststatus = backup_cron_automated_helper::BACKUP_STATUS_SKIPPED;
                    $DB->update_record('backup_courses', $backupcourse);
                    $skipped = true;
                } else if ($backupcourse->nextstarttime > 0 && $backupcourse->nextstarttime < $now) {
                    mtrace('Backing up '.$course->fullname, '...');

                    //We have to send a email because we have included at least one backup
                    $emailpending = true;
                    
                    //Only make the backup if laststatus isn't 2-UNFINISHED (uncontrolled error)
                    if ($backupcourse->laststatus != 2) {
                        //Set laststarttime
                        $starttime = time();

                        $backupcourse->laststarttime = time();
                        $backupcourse->laststatus = backup_cron_automated_helper::BACKUP_STATUS_UNFINISHED;
                        $DB->update_record('backup_courses', $backupcourse);

                        $backupcourse->laststatus = backup_cron_automated_helper::launch_automated_backup($course, $backupcourse->laststarttime, $admin->id);
                        $backupcourse->lastendtime = time();
                        $backupcourse->nextstarttime = $nextstarttime;

                        $DB->update_record('backup_courses', $backupcourse);

                        if ($backupcourse->laststatus) {
                            // Clean up any excess course backups now that we have
                            // taken a successful backup.
                            $removedcount = backup_cron_automated_helper::remove_excess_backups($course);
                        }
                    }

                    mtrace("complete - next execution: $showtime");
                }
            }
            $rs->close();
        }

        //Send email to admin if necessary
        if ($emailpending) {
            mtrace("Sending email to admin");
            $message = "";

            $count = backup_cron_automated_helper::get_backup_status_array();
            $haserrors = ($count[backup_cron_automated_helper::BACKUP_STATUS_ERROR] != 0 || $count[backup_cron_automated_helper::BACKUP_STATUS_UNFINISHED] != 0);

            //Build the message text
            //Summary
            $message .= get_string('summary')."\n";
            $message .= "==================================================\n";
            $message .= "  ".get_string('courses').": ".array_sum($count)."\n";
            $message .= "  ".get_string('ok').": ".$count[backup_cron_automated_helper::BACKUP_STATUS_OK]."\n";
            $message .= "  ".get_string('skipped').": ".$count[backup_cron_automated_helper::BACKUP_STATUS_SKIPPED]."\n";
            $message .= "  ".get_string('error').": ".$count[backup_cron_automated_helper::BACKUP_STATUS_ERROR]."\n";
            $message .= "  ".get_string('unfinished').": ".$count[backup_cron_automated_helper::BACKUP_STATUS_UNFINISHED]."\n\n";

            //Reference
            if ($haserrors) {
                $message .= "  ".get_string('backupfailed')."\n\n";
                $dest_url = "$CFG->wwwroot/$CFG->admin/report/backups/index.php";
                $message .= "  ".get_string('backuptakealook','',$dest_url)."\n\n";
                //Set message priority
                $admin->priority = 1;
                //Reset unfinished to error
                $DB->set_field('backup_courses','laststatus','0', array('laststatus'=>'2'));
            } else {
                $message .= "  ".get_string('backupfinished')."\n";
            }

            //Build the message subject
            $site = get_site();
            $prefix = $site->shortname.": ";
            if ($haserrors) {
                $prefix .= "[".strtoupper(get_string('error'))."] ";
            }
            $subject = $prefix.get_string('automatedbackupstatus', 'backup');

            //Send the message
            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->userfrom          = $admin;
            $eventdata->userto            = $admin;
            $eventdata->subject           = $subject;
            $eventdata->fullmessage       = $message;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';

            $eventdata->component         = 'moodle';
            $eventdata->name         = 'backup';

            message_send($eventdata);
        }

        //Everything is finished stop backup_auto_running
        backup_cron_automated_helper::set_state_running(false);

        mtrace('Automated backups complete.');

        return $status;
    }

    /**
     * Gets the results from the last automated backup that was run based upon
     * the statuses of the courses that were looked at.
     *
     * @global moodle_database $DB
     * @return array
     */
    public static function get_backup_status_array() {
        global $DB;

        $result = array(
            self::BACKUP_STATUS_ERROR => 0,
            self::BACKUP_STATUS_OK => 0,
            self::BACKUP_STATUS_UNFINISHED => 0,
            self::BACKUP_STATUS_SKIPPED => 0,
        );

        $statuses = $DB->get_records_sql('SELECT DISTINCT bc.laststatus, COUNT(bc.courseid) statuscount FROM {backup_courses} bc GROUP BY bc.laststatus');

        foreach ($statuses as $status) {
            if (empty($status->statuscount)) {
                $status->statuscount = 0;
            }
            $result[(int)$status->laststatus] += $status->statuscount;
        }

        return $result;
    }

    /**
     * Works out the next time the automated backup should be run.
     *
     * @param mixed $timezone
     * @param int $now
     * @return int
     */
    public static function calculate_next_automated_backup($timezone, $now) {

        $result = -1;
        $config = get_config('backup');
        $midnight = usergetmidnight($now, $timezone);
        $date = usergetdate($now, $timezone);
        
        //Get number of days (from today) to execute backups
        $automateddays = substr($config->backup_auto_weekdays,$date['wday']) . $config->backup_auto_weekdays;
        $daysfromtoday = strpos($automateddays, "1");
        if (empty($daysfromtoday)) {
            $daysfromtoday = 1;
        }

        //If some day has been found
        if ($daysfromtoday !== false) {
            //Calculate distance
            $dist = ($daysfromtoday * 86400) +                //Days distance
                    ($config->backup_auto_hour * 3600) +      //Hours distance
                    ($config->backup_auto_minute * 60);       //Minutes distance
            $result = $midnight + $dist;
        }

        //If that time is past, call the function recursively to obtain the next valid day
        if ($result > 0 && $result < time()) {
            $result = self::calculate_next_automated_backup($timezone, $result);
        }

        return $result;
    }

    /**
     * Launches a automated backup routine for the given course
     *
     * @param stdClass $course
     * @param int $starttime
     * @param int $userid
     * @return bool
     */
    public static function launch_automated_backup($course, $starttime, $userid) {

        $config = get_config('backup');
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_AUTOMATED, $userid);

        try {

            $settings = array(
                'users' => 'backup_auto_users',
                'role_assignments' => 'backup_auto_users',
                'user_files' => 'backup_auto_user_files',
                'activities' => 'backup_auto_activities',
                'blocks' => 'backup_auto_blocks',
                'filters' => 'backup_auto_filters',
                'comments' => 'backup_auto_comments',
                'completion_information' => 'backup_auto_userscompletion',
                'logs' => 'backup_auto_logs',
                'histories' => 'backup_auto_histories'
            );
            foreach ($settings as $setting => $configsetting) {
                if ($bc->get_plan()->setting_exists($setting)) {
                    $bc->get_plan()->get_setting($setting)->set_value($config->{$configsetting});
                }
            }

            // Set the default filename
            $format = $bc->get_format();
            $type = $bc->get_type();
            $id = $bc->get_id();
            $users = $bc->get_plan()->get_setting('users')->get_value();
            $anonymised = $bc->get_plan()->get_setting('anonymize')->get_value();
            $bc->get_plan()->get_setting('filename')->set_value(backup_plan_dbops::get_default_backup_filename($format, $type, $id, $users, $anonymised));
            
            $bc->set_status(backup::STATUS_AWAITING);

            $outcome = $bc->execute_plan();
            $results = $bc->get_results();
            $file = $results['backup_destination'];
            $dir = $config->backup_auto_destination;
            $storage = (int)$config->backup_auto_storage;
            if (!file_exists($dir) || !is_dir($dir) || !is_writable($dir)) {
                $dir = null;
            }
            if (!empty($dir) && $storage !== 0) {
                $filename = self::get_external_filename($course->id, $format, $type, $users, $anonymised);
                $outcome = $file->copy_content_to($dir.'/'.$filename);
                if ($outcome && $storage === 1) {
                    $file->delete();
                }
            }

        } catch (backup_exception $e) {
            $bc->log('backup_auto_failed_on_course', backup::LOG_WARNING, $course->shortname);
            return false;
        }

        return true;
    }

    /**
     * Gets the filename to use for the backup when it is being moved to an
     * external location.
     *
     * Note: we use the course id in the filename rather than the course shortname
     * because it may contain UTF-8 characters that could cause problems for the
     * recieving filesystem.
     *
     * @param int $courseid
     * @param string $format One of backup::FORMAT_
     * @param string $type One of backup::TYPE_
     * @param bool $users Should be true is users were included in the backup
     * @param bool $anonymised Should be true is user information was anonymized.
     * @return string The filename to use
     */
    public static function get_external_filename($courseid, $format, $type, $users, $anonymised) {
        $backupword = str_replace(' ', '_', moodle_strtolower(get_string('backupfilename')));
        $backupword = trim(clean_filename($backupword), '_');
        // Calculate date
        $backupdateformat = str_replace(' ', '_', get_string('backupnameformat', 'langconfig'));
        $date = userdate(time(), $backupdateformat, 99, false);
        $date = moodle_strtolower(trim(clean_filename($date), '_'));
        // Calculate info
        $info = '';
        if (!$users) {
            $info = 'nu';
        } else if ($anonymised) {
            $info = 'an';
        }
        return $backupword.'-'.$format.'-'.$type.'-'.$courseid.'-'.$date.'-'.$info.'.mbz';
    }

    /**
     * Removes deleted courses fromn the backup_courses table so that we don't
     * waste time backing them up.
     *
     * @global moodle_database $DB
     * @return int
     */
    public static function remove_deleted_courses_from_schedule() {
        global $DB;
        $skipped = 0;
        $sql = "SELECT bc.courseid FROM {backup_courses} bc WHERE bc.courseid NOT IN (SELECT c.id FROM {course} c)";
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $deletedcourse) {
            //Doesn't exist, so delete from backup tables
            $DB->delete_records('backup_courses', array('courseid'=>$deletedcourse->courseid));
            $skipped++;
        }
        $rs->close();
        return $skipped;
    }

    /**
     * Gets the state of the automated backup system.
     *
     * @global moodle_database $DB
     * @return int One of self::STATE_*
     */
    public static function get_automated_backup_state() {
        global $DB;

        $config = get_config('backup');
        if (empty($config->backup_auto_active)) {
            return self::STATE_DISABLED;
        } else if (!empty($config->backup_auto_running)) {
            // TODO: We should find some way of checking whether the automated
            // backup has infact finished. In 1.9 this was being done by checking
            // the log entries.
            return self::STATE_RUNNING;
        }
        return self::STATE_OK;
    }

    /**
     * Sets the state of the automated backup system.
     *
     * @param bool $running
     * @return bool
     */
    public static function set_state_running($running = true) {
        if ($running === true) {
            if (self::get_automated_backup_state() === self::STATE_RUNNING) {
                throw new backup_exception('backup_automated_already_running');
            }
            set_config('backup_auto_running', '1', 'backup');
        } else {
            unset_config('backup_auto_running', 'backup');
        }
        return true;
    }

    /**
     * Removes excess backups from the external system and the local file system.
     *
     * The number of backups keep comes from $config->backup_auto_keep
     *
     * @param stdClass $course
     * @return bool
     */
    public static function remove_excess_backups($course) {
        $config = get_config('backup');
        $keep =     (int)$config->backup_auto_keep;
        $storage =  $config->backup_auto_storage;
        $dir =      $config->backup_auto_destination;

        $backupword = str_replace(' ', '_', moodle_strtolower(get_string('backupfilename')));
        $backupword = trim(clean_filename($backupword), '_');

        if (!file_exists($dir) || !is_dir($dir) || !is_writable($dir)) {
            $dir = null;
        }

        // Clean up excess backups in the course backup filearea
        if ($storage == 0 || $storage == 2) {
            $fs = get_file_storage();
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
            $component = 'backup';
            $filearea = 'automated';
            $itemid = 0;
            $files = array();
            foreach ($fs->get_area_files($context->id, $component, $filearea, $itemid) as $file) {
                if (strpos($file->get_filename(), $backupword) !== 0) {
                    continue;
                }
                $files[$file->get_timemodified()] = $file;
            }
            arsort($files);
            $remove = array_splice($files, $keep);
            foreach ($remove as $file) {
                $file->delete();
            }
            //mtrace('Removed '.count($remove).' old backup file(s) from the data directory');
        }

        // Clean up excess backups in the specified external directory
        if (!empty($dir) && ($storage == 1 || $storage == 2)) {
            // Calculate backup filename regex
            
            $filename = $backupword . '-' . backup::FORMAT_MOODLE . '-' . backup::TYPE_1COURSE . '-' .$course->id . '-';

            $regex = '#^'.preg_quote($filename, '#').'(\d{8})\-(\d{4})\-[a-z]{2}\.mbz$#S';

            $files = array();
            foreach (scandir($dir) as $file) {
                if (preg_match($regex, $file, $matches)) {
                    $files[$file] = $matches[1].$matches[2];
                }
            }
            if (count($files) <= $keep) {
                // There are less matching files than the desired number to keep
                // do there is nothing to clean up.
                return 0;
            }
            arsort($files);
            $remove = array_splice($files, $keep);
            foreach (array_keys($remove) as $file) {
                unlink($dir.'/'.$file);
            }
            //mtrace('Removed '.count($remove).' old backup file(s) from external directory');
        }

        return true;
    }
}