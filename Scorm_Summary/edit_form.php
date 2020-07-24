<?php
 
class block_scormsum_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));
 
        // A sample string variable with a default value.
        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_scormsum'));
        $mform->setDefault('config_title', 'default value');
        $mform->setType('config_title', PARAM_TEXT);        
        //change the default title.

        // A sample string variable with a default value.
        $mform->addElement('text', 'config_text', get_string('blockstring', 'block_myscorm'));
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_RAW);  
             
    }
}