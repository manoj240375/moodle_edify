<?php
/**
 * @package questionbank
 * @subpackage questiontypes
 */

class question_dataset_dependent_definitions_form extends moodleform {
    /**
     * Question object with options and answers already loaded by get_question_options
     * Be careful how you use this it is needed sometimes to set up the structure of the
     * form in definition_inner but data is always loaded into the form with set_defaults.
     *
     * @var object
     */
    var $question;
    /**
     * Reference to question type object
     *
     * @var question_dataset_dependent_questiontype
     */
    var $qtypeobj;
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    function question_dataset_dependent_definitions_form($submiturl, $question){
        global $QTYPES, $DB;
        $this->question = $question;
        $this->qtypeobj =& $QTYPES[$this->question->qtype];
        // Validate the question category.
        if (!$category = $DB->get_record('question_categories', array('id' => $question->category))) {
            print_error('categorydoesnotexist', 'question', $returnurl);
        }
        $this->category = $category;
        $this->categorycontext = get_context_instance_by_id($category->contextid);
        parent::moodleform($submiturl);
    }
    function definition() {
        global $SESSION;
        $mform =& $this->_form;
        $possibledatasets = $this->qtypeobj->find_dataset_names($this->question->questiontext);
        $mandatorydatasets = array();
        if (isset($this->question->options->answers)){
            foreach ($this->question->options->answers as $answer) {
                $mandatorydatasets += $this->qtypeobj->find_dataset_names($answer->answer);
            }
        }else{
            foreach ($SESSION->calculated->questionform->answers as $answer){
                $mandatorydatasets += $this->qtypeobj->find_dataset_names($answer);
            }
        }

        $key = 0;
        $datadefscat= array();
        $datadefscat  = $this->qtypeobj->get_dataset_definitions_category($this->question);
        $datasetmenus = array();
        $label = "<div class='mdl-align'>".get_string('datasetrole', 'qtype_calculated')."</div>";
        $mform->addElement('html', $label);// explaining the role of datasets so other strings can be shortened
        $mform->addElement('header', 'mandatoryhdr', get_string('mandatoryhdr', 'qtype_calculated'));
        $labelsharedwildcard = get_string('sharedwildcard', 'qtype_calculated');

        foreach ($mandatorydatasets as $datasetname) {
            if (!isset($datasetmenus[$datasetname])) {
                list($options, $selected) =
                        $this->qtypeobj->dataset_options($this->question, $datasetname);
                unset($options['0']); // Mandatory...
                $label = get_string("wildcard", "quiz"). " <strong>$datasetname</strong> ";
                $mform->addElement('select', "dataset[$key]", $label, $options);
             if (isset($datadefscat[$datasetname])){
                  $mform->addElement('static', "there is a category", get_string('sharedwildcard', 'qtype_calculated',$datasetname ), get_string('dataitemdefined','qtype_calculated', $datadefscat[$datasetname]));
            }
                $mform->setDefault("dataset[$key]", $selected);
                $datasetmenus[$datasetname]='';
                $key++;
            }
        }
                        $mform->addElement('header', 'possiblehdr', get_string('possiblehdr', 'qtype_calculated'));


        foreach ($possibledatasets as $datasetname) {
            if (!isset($datasetmenus[$datasetname])) {
                list($options, $selected) =
                        $this->qtypeobj->dataset_options($this->question, $datasetname,false);
                $label = get_string("wildcard", "quiz"). " <strong>$datasetname</strong> ";
                $mform->addElement('select', "dataset[$key]", $label, $options);
                 //       $mform->addRule("dataset[$key]", null, 'required', null, 'client');
             if (isset($datadefscat[$datasetname])){
                  $mform->addElement('static', "there is a category", get_string('sharedwildcard', 'qtype_calculated',$datasetname ), get_string('dataitemdefined',"qtype_calculated", $datadefscat[$datasetname]));
            }

              //   $selected ="0";
                $mform->setDefault("dataset[$key]", $selected);
                $datasetmenus[$datasetname]='';
                $key++;
            }
        }
        // temporary strings
        // temporary strings
        $mform->addElement('header', 'synchronizehdr', get_string('synchronize', 'qtype_calculated'));
        $mform->addElement('radio', 'synchronize', '', get_string('synchronizeno', 'qtype_calculated'),0);
        $mform->addElement('radio', 'synchronize', '', get_string('synchronizeyes', 'qtype_calculated'),1);
        $mform->addElement('radio', 'synchronize', '', get_string('synchronizeyesdisplay', 'qtype_calculated'),2);
        if (isset($this->question->options)&& isset($this->question->options->synchronize) ){
            $mform->setDefault("synchronize", $this->question->options->synchronize);
        } else {
            $mform->setDefault("synchronize", 0 );
        }

        $this->add_action_buttons(false, get_string('nextpage', 'qtype_calculated'));


        //hidden elements
        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_URL);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'category');
        $mform->setType('category', PARAM_RAW);
        $mform->setDefault('category', array('contexts' => array($this->categorycontext)));

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', 0);

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->setDefault('cmid', 0);

        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'wizard', 'datasetitems');
        $mform->setType('wizard', PARAM_ALPHA);
    }
/*    function set_data($question) {
        $formdata = array();
        $fromform = new stdClass();
                  $default_values['synchronize'] = $question->options->synchronize ;
        $question = (object)((array)$question + $default_values);
    }*/
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $datasets = $data['dataset'];
        $countvalid = 0 ;
        foreach ($datasets as $key => $dataset){
            if ($dataset !="0") {
                $countvalid++;
            }
        }
        if (!$countvalid){
            foreach ($datasets as $key => $dataset){
                $errors['dataset['.$key.']'] = get_string('atleastonerealdataset', 'qtype_calculated');
            }
       }
        return $errors;
    }

}

