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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Final task that provides all the final steps necessary in order to finish one
 * restore like gradebook, interlinks... apart from some final cleaning
 *
 * TODO: Finish phpdocs
 */
class restore_final_task extends restore_task {

    /**
     * Create all the steps that will be part of this task
     */
    public function build() {

        // Move all the CONTEXT_MODULE question qcats to their
        // final (newly created) module context
        $this->add_step(new restore_move_module_questions_categories('move_module_question_categories'));

        // Create all the question files now that every question is in place
        // and every category has its final contextid associated
        $this->add_step(new restore_create_question_files('create_question_files'));

        // Review all the block_position records in backup_ids in order
        // match them now that all the contexts are created populating DB
        // as needed. Only if we are restoring blocks.
        if ($this->get_setting_value('blocks')) {
            $this->add_step(new restore_review_pending_block_positions('review_block_positions'));
        }

        // Gradebook. Don't restore the gradebook unless activities are being restored.
        if ($this->get_setting_value('activities')) {
            $this->add_step(new restore_gradebook_structure_step('gradebook_step','gradebook.xml'));
        }

        // Course completion
        $this->add_step(new restore_course_completion_structure_step('course_completion', 'completion.xml'));

        // Review all the module_availability records in backup_ids in order
        // to match them with existing modules / grade items.
        $this->add_step(new restore_process_course_modules_availability('process_modules_availability'));

        // Decode all the interlinks
        $this->add_step(new restore_decode_interlinks('decode_interlinks'));

        // Rebuild course cache to see results, whoah!
        $this->add_step(new restore_rebuild_course_cache('rebuild_course_cache'));

        // Review all the executed tasks having one after_restore method
        // executing it to perform some final adjustments of information
        // not available when the task was executed.
        // This step is always the last one before the one cleaning the temp stuff!
        $this->add_step(new restore_execute_after_restore('executing_after_restore'));

        // Clean the temp dir (conditionally) and drop temp table
        $this->add_step(new restore_drop_and_clean_temp_stuff('drop_and_clean_temp_stuff'));

        $this->built = true;
    }

    /**
     * Special method, only available in the restore_final_task, able to invoke the
     * restore_plan execute_after_restore() method, so restore_execute_after_restore step
     * will be able to launch all the after_restore() methods of the executed tasks
     */
    public function launch_execute_after_restore() {
        $this->plan->execute_after_restore();
    }

// Protected API starts here

    /**
     * Define the common setting that any restore type will have
     */
    protected function define_settings() {
        // This task has not settings (could have them, like destination or so in the future, let's see)
    }
}
