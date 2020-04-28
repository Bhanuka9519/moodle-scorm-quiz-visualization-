<?php
   require_once(dirname(__FILE__) . '/../../config.php');
   require_once($CFG->dirroot.'/blocks/tracker2/lib.php');
   

   define('USER_SMALL_CLASS', 20);   
   define('USER_LARGE_CLASS', 200);  
   define('DEFAULT_PAGE_SIZE', 20);
   define('SHOW_ALL_PAGE_SIZE', 5000);

   $id              = required_param('logingraphid',    PARAM_INT);
   $courseid        = required_param('courseid',   PARAM_INT);
   $userid          = required_param('userid',     PARAM_INT);

   $page            = optional_param('page', 0,    PARAM_INT); 
   $perpage         = optional_param('perpage',    DEFAULT_PAGE_SIZE, PARAM_INT); 
   $group           = optional_param('group', 0,   PARAM_INT); 

   $course          = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
   $context         = block_tracker2_course_context($courseid);

   $loginblock      = $DB->get_record('block_instances', array('id' => $id), '*', MUST_EXIST);
   $loginsconfig    = unserialize(base64_decode($loginblock->configdata));

   $PAGE->set_course($course);

   $PAGE->set_url(
       '/blocks/tracker2/overview.php',
       array(
           'logingraphid'    => $id,
           'courseid'   => $courseid,
           'page'       => $page,
           'perpage'    => $perpage,
           'group'      => $group,
       )
   );

   $PAGE    ->  set_context($context);
   $title = 'Time Spent per Lesson';
   $PAGE    ->  set_title($title);      //sets title in title-bar
   $PAGE    ->  set_heading($title);    //sets title in header
   $PAGE    ->  navbar->add($title);    //adds title to navbar
  
   require_login($course, true);

   echo $OUTPUT->header();

   echo $OUTPUT->container_start('block_tracker2');
   echo '<div>';

    global $DB;

    //connect scormid and scoid
    $join_scorm_and_scoes = "SELECT id, scorm FROM {scorm_scoes};";
    $joined = $DB->get_records_sql($join_scorm_and_scoes);

    //getting lesson names from db
    $sco_lessons = "SELECT id, name FROM {scorm} WHERE course = $courseid";
    $info_sco_lessons = $DB->get_records_sql($sco_lessons);
    
    $name_array = array();

    //initialize name list for scorm lessons
    $name = array();
    foreach($info_sco_lessons as $sco_name){
        //entering lesson names into array by id
        $name[$sco_name->id]=$sco_name->name;
        array_push($name_array, $sco_name->name);
    }

    //get name of course
    $fullname = "SELECT fullname FROM {course} WHERE id=$courseid";
    $coursename = $DB->get_records_sql($fullname);
    foreach($coursename as $info_coursename){
        $course_name=$info_coursename->fullname;
    }

    //create a new chart
    $chart = new \core\chart_line();
    //name its axis
    $chart->get_xaxis(0, true)->set_label("Lessons in ". $course_name); 
    $chart->get_yaxis(0, true)->set_label("Time spent per lesson(hrs)");
    //$chart->set_smooth(true); // Calling set_smooth() passing true as parameter, will display smooth lines.

    //get ids, names of students enrolled in course
    $contextid = get_context_instance(CONTEXT_COURSE, $courseid);

    $users = "SELECT u.id, u.username
                FROM {user} u, {role_assignments} r
                WHERE u.id=r.userid
                    AND r.contextid = {$contextid->id}";
    $info_students = $DB->get_records_sql($users);

    $sc=0;
    $sc1=0;

    $stu_name = array();

    foreach($info_students as $user_info){

        //entering user names into array by id
        $stu_name[$user_info->id]=$user_info->username;

        //at loop for each student initializes an array with 
            //no. of elements=number of scorm packages, 
            //each element=0
        $array=array();
        $sco_lessons = "SELECT id FROM {scorm} WHERE course = $courseid";
        $info_sco_lessons = $DB->get_records_sql($sco_lessons);
        foreach($info_sco_lessons as $sco_id){
            array_push($array, 0);
        }

        //find which scorm packages each student has accessed
        $sql = "SELECT sst.scormid, sst.scoid, sst.value 
        FROM {scorm_scoes_track} sst, {scorm} s 
        WHERE sst.scormid=s.id 
            AND element='cmi.core.total_time' 
            AND sst.userid=$user_info->id 
            AND s.course=$courseid;";
        $result = $DB->get_records_sql($sql);

        $sc=0;

        foreach($result as $value){
            $split_time_value = explode (":", $value->value);
            $split_time_value[3]=($split_time_value[0])+($split_time_value[1]/60)+($split_time_value[2]/(60*60));
            if ($split_time_value[3]>0){
                $array[($value->scormid)-1]=$split_time_value[3];
                //$array[$sc]=$split_time_value[3];
            }
            // else{
            //     //$array[($value->scormid)-1]=$split_time_value[3];
            //     $array[$sc]=0;
            // }
            $sc++;
        }
        // echo '<pre>'; print_r($array); echo '</pre>';

        //sets line-chart lines to each student
        $time_per_student = new core\chart_series($user_info->username, $array);
        $chart->add_series($time_per_student);

        //resets array value to 0 to prepare for next student
        $sco_lessons = "SELECT id FROM {scorm} WHERE course = $courseid";
        $info_sco_lessons = $DB->get_records_sql($sco_lessons);
        $sc1=0;
        foreach($info_sco_lessons as $sco_id){
            $array[$sc1]=0;
        }
    }

    $chart->set_labels($name_array);
    echo $OUTPUT->render($chart);

    echo '</div>';

   echo $OUTPUT->container_end();

   echo $OUTPUT->footer();

   