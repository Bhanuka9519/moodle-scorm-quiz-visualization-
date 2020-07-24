<html>
    
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="scormsumcss.css" />
</head>




<?php

require_once(dirname(__FILE__) . '/../../config.php');

class block_scormsum extends block_base {
    public function init() {
        $this->title = 'Scorm quizzes marks summary';
    }


    public function get_content() {
        global $USER, $COURSE,$OUTPUT;

        if ($this->content !== null) {
          return $this->content;
        }
     
        $this->content         =  new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        $parameters = array('scormsumid' => $this->instance->id, 'courseid' => $COURSE->id, 'userid' => $USER->id);
        $options = array('class' => 'OverviweButton');
        $url = new moodle_url('/blocks/myscorm/overview.php',$parameters);
        $this->content->text .= $OUTPUT->single_button($url, 'overviwe of student marks', 'post',$options);
     
        return $this->content;
    }

    public function specialization() {
        if (isset($this->config)) {
               
        }
    }

    public function instance_allow_multiple() {
        return true;
    }

    function has_config(){
        return true;
    }  

    public function applicable_formats(){
        return array(
            'course-view'    => true,  
            'site'           => true,
            'mod'            => false,
            'my'             => true        
        );
    }

}
