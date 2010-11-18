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

defined('MOODLE_INTERNAL') || die();

/////////////////
/// TRUEFALSE ///
/////////////////

/// QUESTION TYPE CLASS //////////////////
/**
 * @package questionbank
 * @subpackage questiontypes
 */
class question_truefalse_qtype extends default_questiontype {

    function name() {
        return 'truefalse';
    }

    function save_question_options($question) {
        global $DB;
        $result = new stdClass;
        $fs = get_file_storage();

        // fetch old answer ids so that we can reuse them
        if (!$oldanswers = $DB->get_records("question_answers", array("question" =>  $question->id), "id ASC")) {
            $oldanswers = array();
        }

        $feedbacktext = $question->feedbacktrue['text'];
        $feedbackitemid = $question->feedbacktrue['itemid'];
        $feedbackformat = $question->feedbacktrue['format'];
        if (isset($question->feedbacktruefiles)) {
            $files = $question->feedbacktruefiles;
        }
        // Save answer 'True'
        if ($true = array_shift($oldanswers)) {  // Existing answer, so reuse it
            $true->answer   = get_string("true", "quiz");
            $true->fraction = $question->correctanswer;
            $true->feedback = $question->feedbacktrue;
            $true->feedback = file_save_draft_area_files($feedbackitemid, $question->context->id, 'question', 'answerfeedback', $true->id, self::$fileoptions, $feedbacktext);
            $true->feedbackformat = $feedbackformat;
            $DB->update_record("question_answers", $true);
        } else {
            unset($true);
            $true = new stdClass();
            $true->answer   = get_string("true", "quiz");
            $true->question = $question->id;
            $true->fraction = $question->correctanswer;
            $true->feedback = '';
            $true->feedbackformat = $feedbackformat;
            $true->id = $DB->insert_record("question_answers", $true);
            // import
            if (isset($files)) {
                foreach ($files as $file) {
                    $this->import_file($question->context, 'question', 'answerfeedback', $true->id, $file);
                }
            } else {
            // moodle form
                $feedbacktext = file_save_draft_area_files($feedbackitemid, $question->context->id, 'question', 'answerfeedback', $true->id, self::$fileoptions, $feedbacktext);
            }
            $DB->set_field('question_answers', 'feedback', $feedbacktext, array('id'=>$true->id));
        }

        $feedbacktext = $question->feedbackfalse['text'];
        $feedbackitemid = $question->feedbackfalse['itemid'];
        $feedbackformat = $question->feedbackfalse['format'];

        // Save answer 'False'
        if ($false = array_shift($oldanswers)) {  // Existing answer, so reuse it
            $false->answer   = get_string("false", "quiz");
            $false->fraction = 1 - (int)$question->correctanswer;
            $false->feedback = file_save_draft_area_files($feedbackitemid, $question->context->id, 'question', 'answerfeedback', $false->id, self::$fileoptions, $feedbacktext);
            $false->feedbackformat = $feedbackformat;
            $DB->update_record("question_answers", $false);
        } else {
            unset($false);
            $false = new stdClass();
            $false->answer   = get_string("false", "quiz");
            $false->question = $question->id;
            $false->fraction = 1 - (int)$question->correctanswer;
            $false->feedback = '';
            $false->feedbackformat = $feedbackformat;
            $false->id = $DB->insert_record("question_answers", $false);
            if ($feedbackitemid == null && !empty($files)) {
                foreach ($files as $file) {
                    $this->import_file($question->context, 'question', 'answerfeedback', $false->id, $file);
                }
            } else {
                $feedbacktext = file_save_draft_area_files($feedbackitemid, $question->context->id, 'question', 'answerfeedback', $false->id, self::$fileoptions, $feedbacktext);
            }
            $DB->set_field('question_answers', 'feedback', $feedbacktext, array('id'=>$false->id));
        }

        // delete any leftover old answer records (there couldn't really be any, but who knows)
        if (!empty($oldanswers)) {
            foreach($oldanswers as $oa) {
                $DB->delete_records('question_answers', array('id' => $oa->id));
            }
        }

        // Save question options in question_truefalse table
        if ($options = $DB->get_record("question_truefalse", array("question" => $question->id))) {
            // No need to do anything, since the answer IDs won't have changed
            // But we'll do it anyway, just for robustness
            $options->trueanswer  = $true->id;
            $options->falseanswer = $false->id;
            $DB->update_record("question_truefalse", $options);
        } else {
            unset($options);
            $options->question    = $question->id;
            $options->trueanswer  = $true->id;
            $options->falseanswer = $false->id;
            $DB->insert_record("question_truefalse", $options);
        }
        return true;
    }

    /**
    * Loads the question type specific options for the question.
    */
    function get_question_options(&$question) {
        global $DB, $OUTPUT;
        // Get additional information from database
        // and attach it to the question object
        if (!$question->options = $DB->get_record('question_truefalse', array('question' => $question->id))) {
            echo $OUTPUT->notification('Error: Missing question options!');
            return false;
        }
        // Load the answers
        if (!$question->options->answers = $DB->get_records('question_answers', array('question' =>  $question->id), 'id ASC')) {
           echo $OUTPUT->notification('Error: Missing question answers for truefalse question ' . $question->id . '!');
           return false;
        }

        return true;
    }

    /**
    * Deletes question from the question-type specific tables
    *
    * @return boolean Success/Failure
    * @param object $question  The question being deleted
    */
    function delete_question($questionid) {
        global $DB;
        $DB->delete_records("question_truefalse", array("question" => $questionid));
        return true;
    }

    function compare_responses($question, $state, $teststate) {
        if (isset($state->responses['']) && isset($teststate->responses[''])) {
            return $state->responses[''] === $teststate->responses[''];
        } else if (isset($teststate->responses['']) && $teststate->responses[''] === '' &&
                !isset($state->responses[''])) {
            // Nothing selected in the past, and nothing selected now.
            return true;
        }
        return false;
    }

    function get_correct_responses(&$question, &$state) {
        // The correct answer is the one which gives full marks
        foreach ($question->options->answers as $answer) {
            if (((int) $answer->fraction) === 1) {
                return array('' => $answer->id);
            }
        }
        return null;
    }

    /**
    * Prints the main content of the question including any interactions
    */
    function print_question_formulation_and_controls(&$question, &$state, $cmoptions, $options) {
        global $CFG;
        $context = $this->get_context_by_category_id($question->category);

        $readonly = $options->readonly ? ' disabled="disabled"' : '';

        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->para = false;

        // Print question formulation
        $questiontext = format_text($question->questiontext,
                         $question->questiontextformat,
                         $formatoptions, $cmoptions->course);

        $answers = &$question->options->answers;
        $trueanswer = &$answers[$question->options->trueanswer];
        $falseanswer = &$answers[$question->options->falseanswer];
        $correctanswer = ($trueanswer->fraction == 1) ? $trueanswer : $falseanswer;

        $trueclass = '';
        $falseclass = '';
        $truefeedbackimg = '';
        $falsefeedbackimg = '';

        // Work out which radio button to select (if any)
        if (isset($state->responses[''])) {
            $response = $state->responses[''];
        } else {
            $response = '';
        }
        $truechecked = ($response == $trueanswer->id) ? ' checked="checked"' : '';
        $falsechecked = ($response == $falseanswer->id) ? ' checked="checked"' : '';

        // Work out visual feedback for answer correctness.
        if ($options->feedback) {
            if ($truechecked) {
                $trueclass = question_get_feedback_class($trueanswer->fraction);
            } else if ($falsechecked) {
                $falseclass = question_get_feedback_class($falseanswer->fraction);
            }
        }
        if ($options->feedback || $options->correct_responses) {
            if (isset($answers[$response])) {
                $truefeedbackimg = question_get_feedback_image($trueanswer->fraction, !empty($truechecked) && $options->feedback);
                $falsefeedbackimg = question_get_feedback_image($falseanswer->fraction, !empty($falsechecked) && $options->feedback);
            }
        }

        $inputname = ' name="'.$question->name_prefix.'" ';
        $trueid    = $question->name_prefix.'true';
        $falseid   = $question->name_prefix.'false';

        $radiotrue = '<input type="radio"' . $truechecked . $readonly . $inputname
            . 'id="'.$trueid . '" value="' . $trueanswer->id . '" /><label for="'.$trueid . '">'
            . s($trueanswer->answer) . '</label>';
        $radiofalse = '<input type="radio"' . $falsechecked . $readonly . $inputname
            . 'id="'.$falseid . '" value="' . $falseanswer->id . '" /><label for="'.$falseid . '">'
            . s($falseanswer->answer) . '</label>';

        $feedback = '';
        if ($options->feedback and isset($answers[$response])) {
            $chosenanswer = $answers[$response];
            $chosenanswer->feedback = quiz_rewrite_question_urls($chosenanswer->feedback, 'pluginfile.php', $context->id, 'question', 'answerfeedback', array($state->attempt, $state->question), $chosenanswer->id);
            $feedback = format_text($chosenanswer->feedback, true, $formatoptions, $cmoptions->course);
        }

        include("$CFG->dirroot/question/type/truefalse/display.html");
    }

    function check_file_access($question, $state, $options, $contextid, $component,
            $filearea, $args) {
        if ($component == 'question' && $filearea == 'answerfeedback') {

            $answerid = reset($args); // itemid is answer id.
            $answers = &$question->options->answers;
            if (isset($state->responses[''])) {
                $response = $state->responses[''];
            } else {
                $response = '';
            }

            return $options->feedback && isset($answers[$response]) && $answerid == $response;

        } else {
            return parent::check_file_access($question, $state, $options, $contextid, $component,
                    $filearea, $args);
        }
    }

    function grade_responses(&$question, &$state, $cmoptions) {
        if (isset($state->responses['']) && isset($question->options->answers[$state->responses['']])) {
            $state->raw_grade = $question->options->answers[$state->responses['']]->fraction * $question->maxgrade;
        } else {
            $state->raw_grade = 0;
        }
        // Only allow one attempt at the question
        $state->penalty = 1 * $question->maxgrade;

        // mark the state as graded
        $state->event = ($state->event ==  QUESTION_EVENTCLOSE) ? QUESTION_EVENTCLOSEANDGRADE : QUESTION_EVENTGRADE;

        return true;
    }

    function get_actual_response($question, $state) {
        if (isset($question->options->answers[$state->responses['']])) {
            $responses[] = $question->options->answers[$state->responses['']]->answer;
        } else {
            $responses[] = '';
        }
        return $responses;
    }
    /**
     * @param object $question
     * @return mixed either a integer score out of 1 that the average random
     * guess by a student might give or an empty string which means will not
     * calculate.
     */
    function get_random_guess_score($question) {
        return 0.5;
    }

    /**
     * Runs all the code required to set up and save an essay question for testing purposes.
     * Alternate DB table prefix may be used to facilitate data deletion.
     */
    function generate_test($name, $courseid = null) {
        global $DB;
        list($form, $question) = parent::generate_test($name, $courseid);
        $question->category = $form->category;

        $form->questiontext = "This question is really stupid";
        $form->penalty = 1;
        $form->defaultgrade = 1;
        $form->correctanswer = 0;
        $form->feedbacktrue = 'Can you justify such a hasty judgment?';
        $form->feedbackfalse = 'Wisdom has spoken!';

        if ($courseid) {
            $course = $DB->get_record('course', array('id' => $courseid));
        }

        return $this->save_question($question, $form, $course);
    }
    /**
     * When move the category of questions, the belonging files should be moved as well
     * @param object $question, question information
     * @param object $newcategory, target category information
     */
    function move_files($question, $newcategory) {
        global $DB;
        parent::move_files($question, $newcategory);

        $fs = get_file_storage();
        // process files in answer
        if (!$oldanswers = $DB->get_records('question_answers', array('question' =>  $question->id), 'id ASC')) {
            $oldanswers = array();
        }
        $component = 'question';
        $filearea = 'answerfeedback';
        foreach ($oldanswers as $answer) {
            $files = $fs->get_area_files($question->contextid, $component, $filearea, $answer->id);
            foreach ($files as $storedfile) {
                if (!$storedfile->is_directory()) {
                    $newfile = new stdClass();
                    $newfile->contextid = (int)$newcategory->contextid;
                    $fs->create_file_from_storedfile($newfile, $storedfile);
                    $storedfile->delete();
                }
            }
        }
    }
}
//// END OF CLASS ////

//////////////////////////////////////////////////////////////////////////
//// INITIATION - Without this line the question type is not in use... ///
//////////////////////////////////////////////////////////////////////////
question_register_questiontype(new question_truefalse_qtype());
