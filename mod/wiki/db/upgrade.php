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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to the wiki module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * @package mod-wiki-2.0
 * @copyrigth 2009 Marc Alier, Jordi Piguillem marc.alier@upc.edu
 * @copyrigth 2009 Universitat Politecnica de Catalunya http://www.upc.edu
 *
 * @author Jordi Piguillem
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

/**
 *
 * TODO LIST:
 *
 * 1. Add needed fields to wiki table. DONE
 * 2. Rename other wiki tables. DONE
 * 3. Create new wiki tables. DONE BUT NOT FINISHED, WATING FOR NEW TABLES
 * 4. Move/Adapt/Transform configurations info to new structure
 * 5. Migrate wiki entries to subwikis. DONE
 * 6. Fill pages table with latest versions of every page. DONE
 * 7. Migrate page history to new table (transforming formats). DONE, BUT STILL WORKING
 * 8. Fill links table
 * 9. Drop useless information
 *
 * ADITIONAL THINGS AFTER CHAT WITH ELOY:
 *
 * 1. addField is deprecated. DONE
 * 2. Fix SQL error at block 3. DONE
 * 3. Merge set_field_select with previous update sentence. DONE
 * 4. Don't insert id fields on database (it won't work on mssql, oracle, pg). DONE.
 * 5. Use upgrade_set_timeout function.
 * 6. Use grafic of progess
 *
 * OTHER THINGS:
 *
 * 1. Use recordset instead of record when migrating historic
 * 2. Select only usefull data on block 06
 *
 */

function xmldb_wiki_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    // Step 0: Add new fields to main wiki table
    if ($oldversion < 2010040100) {
        require_once(dirname(__FILE__) . '/upgradelib.php');
        echo $OUTPUT->notification('Adding new fields to wiki table', 'notifysuccess');
        wiki_add_wiki_fields();

        upgrade_mod_savepoint(true, 2010040100, 'wiki');
    }

    // Step 1: Rename old tables
    if ($oldversion < 2010040101) {
        $tables = array('wiki_pages', 'wiki_locks', 'wiki_entries');

        echo $OUTPUT->notification('Renaming old wiki module tables', 'notifysuccess');
        foreach ($tables as $tablename) {
            $table = new xmldb_table($tablename);
            if ($dbman->table_exists($table)) {
                if ($dbman->table_exists($table)) {
                    $dbman->rename_table($table, $tablename . '_old');
                }
            }
        }
        upgrade_mod_savepoint(true, 2010040101, 'wiki');
    }

    // Step 2: Creating new tables
    if ($oldversion < 2010040102) {
        require_once(dirname(__FILE__) . '/upgradelib.php');
        echo $OUTPUT->notification('Installing new wiki module tables', 'notifysuccess');
        wiki_upgrade_install_20_tables();
        upgrade_mod_savepoint(true, 2010040102, 'wiki');
    }

    // Step 3: migrating wiki instances
    if ($oldversion < 2010040103) {
        upgrade_set_timeout();

        // Setting up wiki configuration
        $sql = "UPDATE {wiki}
                    SET intro = summary,
                    firstpagetitle = pagename,
                    defaultformat = ?";
        $DB->execute($sql, array('html'));

        $sql = "UPDATE {wiki}
                    SET wikimode = ?
                    WHERE wtype = ?";
        $DB->execute($sql, array('collaborative', 'group'));

        $sql = "UPDATE {wiki}
                    SET wikimode = ?
                    WHERE wtype != ?";
        $DB->execute($sql, array('individual', 'group'));

        // Removing edit & create capability to students in old teacher wikis
        $studentroles = $DB->get_records('role', array('archetype' => 'student'));
        $wikis = $DB->get_records('wiki');
        foreach ($wikis as $wiki) {
            echo $OUTPUT->notification('Migrating '.$wiki->wtype.' type wiki instance: '.$wiki->name, 'notifysuccess');
            if ($wiki->wtype == 'teacher') {
                $cm = get_coursemodule_from_instance('wiki', $wiki->id);
                $context = get_context_instance(CONTEXT_MODULE, $cm->id);
                foreach ($studentroles as $studentrole) {
                    role_change_permission($studentrole->id, $context, 'mod/wiki:editpage', CAP_PROHIBIT);
                    role_change_permission($studentrole->id, $context, 'mod/wiki:createpage', CAP_PROHIBIT);
                }
            }
        }

        echo $OUTPUT->notification('Migrating old wikis to new wikis', 'notifysuccess');
        upgrade_mod_savepoint(true, 2010040103, 'wiki');
    }

    // Step 4: migrating wiki entries to new subwikis
    if ($oldversion < 2010040104) {
        /**
         * Migrating wiki entries to new subwikis
         */
        $sql = "INSERT into {wiki_subwikis} (wikiid, groupid, userid)
                    SELECT e.wikiid, e.groupid, e.userid
                    FROM {wiki_entries_old} e";
        echo $OUTPUT->notification('Migrating old entries to new subwikis', 'notifysuccess');

        $DB->execute($sql, array());

        upgrade_mod_savepoint(true, 2010040104, 'wiki');
    }

    // Step 5: Migrating pages
    if ($oldversion < 2010040105) {
        /**
         * Filling pages table with latest versions of every page.
         *
         * @TODO:   Ensure that ALL versions of every page are always in database and
         *          they can be removed or cleaned.
         *          That fact could let us rewrite the subselect to execute a count(*) to avoid
         *          the order by and it would be much faster.
         */

        $sql = "INSERT into {wiki_pages} (subwikiid, title, cachedcontent, timecreated, timemodified, userid, pageviews)
                    SELECT s.id, p.pagename, ?, p.created, p.lastmodified, p.userid, p.hits
                    FROM {wiki_pages_old} p
                    LEFT OUTER JOIN {wiki_entries_old} e ON e.id = p.wiki
                    LEFT OUTER JOIN {wiki_subwikis} s
                    ON s.wikiid = e.wikiid AND s.groupid = e.groupid AND s.userid = e.userid
                    WHERE p.version = (
                        SELECT max(po.version)
                        FROM {wiki_pages_old} po
                        WHERE p.pagename = po.pagename and
                        p.wiki = po.wiki
                    )";
        echo $OUTPUT->notification('Migrating old pages to new pages', 'notifysuccess');

        $DB->execute($sql, array('**reparse needed**'));

        upgrade_mod_savepoint(true, 2010040105, 'wiki');
    }

    // Step 6: Migrating versions
    if ($oldversion < 2010040106) {
        require_once(dirname(__FILE__) . '/upgradelib.php');
        echo $OUTPUT->notification('Migrating old history to new history', 'notifysuccess');
        wiki_upgrade_migrate_versions();
        upgrade_mod_savepoint(true, 2010040106, 'wiki');
    }

    // Step 7: refresh cachedcontent and fill wiki links table
    if ($oldversion < 2010040107) {
        require_once($CFG->dirroot. '/mod/wiki/locallib.php');
        upgrade_set_timeout();

        $pages = $DB->get_recordset('wiki_pages');

        foreach ($pages as $page) {
            wiki_refresh_cachedcontent($page);
        }

        $pages->close();

        echo $OUTPUT->notification('Caching content', 'notifysuccess');
        upgrade_mod_savepoint(true, 2010040107, 'wiki');
    }
    // Step 8, migrating files
    if ($oldversion < 2010040108) {
        $fs = get_file_storage();
        $sql = "SELECT DISTINCT po.pagename, w.id AS wikiid, po.userid,
                    po.meta AS filemeta, eo.id AS entryid, eo.groupid, s.id AS subwiki,
                    w.course AS courseid, cm.id AS cmid
                    FROM {wiki_pages_old} po
                    LEFT OUTER JOIN {wiki_entries_old} eo
                    ON eo.id=po.wiki
                    LEFT OUTER JOIN {wiki} w
                    ON w.id = eo.wikiid
                    LEFT OUTER JOIN {wiki_subwikis} s
                    ON s.groupid = eo.groupid AND s.wikiid = eo.wikiid AND eo.userid = s.userid
                    JOIN {modules} m ON m.name = 'wiki'
                    JOIN {course_modules} cm ON (cm.module = m.id AND cm.instance = w.id)";

        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $r) {
            if (strpos($r->pagename, 'internal://') !== false) {
                // Found a file resource!
                $pattern = 'internal://';
                $matches = array();
                $filename = str_replace($pattern, '', $r->pagename);
                $orgifilename = $filename = clean_param($filename, PARAM_FILE);
                $context = get_context_instance(CONTEXT_MODULE, $r->cmid);
                $filemeta = unserialize($r->filemeta);
                $filesection = $filemeta['section'];
                // When attach a file to wiki page, user can customize the file name instead of original file name
                // if user did, old wiki will create two pages, internal://original_pagename and internal://renamed_pagename
                // internal://original_pagename record has renamed pagename in meta field
                // but all file have this field
                // old wiki will rename file names to filter space and special character
                if (!empty($filemeta['Content-Location'])) {
                    $orgifilename = urldecode($filemeta['Content-Location']);
                    $orgifilename = str_replace(' ', '_', $orgifilename);
                }
                $thefile = $CFG->dataroot . '/' . $r->courseid . '/moddata/wiki/' . $r->wikiid .'/' . $r->entryid . '/'. $filesection .'/'. $filename;

                if (is_file($thefile) && is_readable($thefile)) {
                    $filerecord = array('contextid' => $context->id,
                                        'component' => 'mod_wiki',
                                        'filearea'  => 'attachments',
                                        'itemid'    => $r->subwiki,
                                        'filepath'  => '/',
                                        'filename'  => $orgifilename,
                                        'userid'    => $r->userid);
                    if (!$fs->file_exists($context->id, 'mod_wiki', 'attachments', $r->subwiki, '/', $orgifilename)) {
                        //echo $OUTPUT->notification('Migrating file '.$orgifilename, 'notifysuccess');
                        $storedfile = $fs->create_file_from_pathname($filerecord, $thefile);
                    }
                    // we have to create another file here to make sure interlinks work
                    if (!$fs->file_exists($context->id, 'mod_wiki', 'attachments', $r->subwiki, '/', $filename)) {
                        $filerecord['filename'] = $filename;
                        //echo $OUTPUT->notification('Migrating file '.$filename, 'notifysuccess');
                        $storedfile = $fs->create_file_from_pathname($filerecord, $thefile);
                    }
                } else {
                    echo $OUTPUT->notification("Bad data found: $r->pagename <br/> Expected file path: $thefile Please fix the bad file path manually.");
                }
            }
        }
        $rs->close();
        upgrade_mod_savepoint(true, 2010040108, 'wiki');
    }

    // Step 9: clean wiki table
    if ($oldversion < 2010040109) {
        $fields = array('summary', 'pagename', 'wtype', 'ewikiprinttitle', 'htmlmode', 'ewikiacceptbinary', 'disablecamelcase', 'setpageflags', 'strippages', 'removepages', 'revertchanges', 'initialcontent');
        $table = new xmldb_table('wiki');
        foreach ($fields as $fieldname) {
            $field = new xmldb_field($fieldname);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }

        }
        echo $OUTPUT->notification('Cleaning wiki table', 'notifysuccess');
        upgrade_mod_savepoint(true, 2010040109, 'wiki');
    }

    // TODO: Will hold the old tables so we will have chance to fix problems
    // Will remove old tables once migrating 100% stable
    // Step 10: delete old tables
    if ($oldversion < 2010040120) {
        //$tables = array('wiki_pages', 'wiki_locks', 'wiki_entries');

        //foreach ($tables as $tablename) {
            //$table = new xmldb_table($tablename . '_old');
            //if ($dbman->table_exists($table)) {
                //$dbman->drop_table($table);
            //}
        //}
        //echo $OUTPUT->notification('Droping old tables', 'notifysuccess');
        //upgrade_mod_savepoint(true, 2010040120, 'wiki');
    }

    if ($oldversion < 2010080201) {

        $sql = "UPDATE {comments}
                SET commentarea = 'wiki_page'
                WHERE commentarea = 'wiki_comment_section'";
        $DB->execute($sql);

        $sql = "UPDATE {tag_instance}
                SET itemtype = 'wiki_page'
                WHERE itemtype = 'wiki'";
        $DB->execute($sql);

        echo $OUTPUT->notification('Updating comments and tags', 'notifysuccess');

        upgrade_mod_savepoint(true, 2010080201, 'wiki');
    }

    if ($oldversion < 2010102500) {

        // Define key subwikifk (foreign) to be added to wiki_pages
        $table = new xmldb_table('wiki_pages');
        $key = new xmldb_key('subwikifk', XMLDB_KEY_FOREIGN, array('subwikiid'), 'wiki_subwikis', array('id'));

        // Launch add key subwikifk
        $dbman->add_key($table, $key);

         // Define key subwikifk (foreign) to be added to wiki_links
        $table = new xmldb_table('wiki_links');
        $key = new xmldb_key('subwikifk', XMLDB_KEY_FOREIGN, array('subwikiid'), 'wiki_subwikis', array('id'));

        // Launch add key subwikifk
        $dbman->add_key($table, $key);

        // wiki savepoint reached
        upgrade_mod_savepoint(true, 2010102500, 'wiki');
    }

    if ($oldversion < 2010102800) {

        $sql = "UPDATE {tag_instance}
                SET itemtype = 'wiki_pages'
                WHERE itemtype = 'wiki_page'";
        $DB->execute($sql);

        echo $OUTPUT->notification('Updating tags itemtype', 'notifysuccess');

        upgrade_mod_savepoint(true, 2010102800, 'wiki');
    }

    return true;
}
