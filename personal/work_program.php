<?php

function personal_teacher_work_program_form($form, &$form_state)
{
	$form['#prefix'] = '<div id="personal-teacher-work-program-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['#tree'] = TRUE;

	global $user;
	$email = $user->mail;

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $teacher_result = $mysqli->query("SELECT *
                 FROM teacher
                 WHERE `E-Mail` = '" . $email . "'");
	$teacher = $teacher_result->fetch_assoc();
	$teacher_result->close();

	$form_state['storage']['teacher_id'] = $teacher['idTeacher'];

	$canteach_result = $mysqli->query("SELECT *
                 FROM canteach
                 WHERE `Teacher` = '" . $teacher['idTeacher'] . "'");
    $rows = array();
    $i = 0;
    $j = 0;
    foreach ($canteach_result as $row) 
    {
    	$discipline_result = $mysqli->query("SELECT `DisFullName`
                 FROM discipline
                 WHERE `idDiscipline` = '" . $row['Discipline'] . "'");

		$discipline = $discipline_result->fetch_assoc();
    	$discipline_result->close();

     	$curriculum_result = $mysqli->query("SELECT `idCurriculumDiscipline`, `Curriculum`
                 FROM curriculumdiscipline
                 WHERE `Discipline` = '" . $row['Discipline'] . "'");

     	
 		foreach ($curriculum_result as $curriculum_dis) 
 		{
 			$rows[$j]['dis_name'] = $discipline['DisFullName'];
 			$rows[$j]['idCurriculumDiscipline'] = $curriculum_dis['idCurriculumDiscipline'];

 			$curriculum_num_result = $mysqli->query("SELECT `CurriculumNum`
                 FROM curriculum
                 WHERE `idCurriculum` = '" . $curriculum_dis['Curriculum'] . "'");

 			$curriculum_num = $curriculum_num_result->fetch_assoc();
     		$curriculum_num_result->close();
     		$rows[$j]['curriculum_num'] = $curriculum_num['CurriculumNum'];

 			$j++;
 		}

 		$curriculum_result->close();
 		
     	$i++;
     } 
    $mysqli->close();
   // debug($rows);
    $step = empty($form_state['storage']['step']) ? 1 : $form_state['storage']['step'];
    $form_state['storage']['step'] = $step;
    $form_state['storage']['rows'] = $rows;

    switch ($step) 
    {
        case 1:

			$form['step1'] = array(
		        '#type' => 'fieldset',
		        '#collapsible' => TRUE, 
				'#collapsed' => TRUE, 
		        '#title' => 'Рабочие программы дисциплин',
		      	);

			$options = array();
			foreach ($rows as $row) 
			{
				$options[] = $row['dis_name'] . ' (' . $row['curriculum_num'] . ')';
			}

			$form['step1']['select_dis'] = array(
				'#type' => 'select', 
				'#title' => 'Выберите дисциплину УП', 
				'#default_value' => 0,
				'#options' => $options,
				);

			$form['step1']['goto'] = array(
				'#type' => 'submit', 
	            '#value' => 'Выбрать',
	            '#submit' => array('personal_teacher_work_program_form_submit'),
	            '#ajax' => array(
		                'wrapper' => 'personal-teacher-work-program-form-wrapper', 
		                'callback' => 'personal_teacher_work_program_ajax_callback',
		                ),
            );

		break;

		case 2:

			$form['step2'] = array(
		        '#type' => 'fieldset',
		        '#collapsible' => TRUE, 
				'#collapsed' => FALSE, 
		        '#title' => 'Рабочие программы дисциплин',
		      	);

			$form['step2']['title'] = array(
				'#markup' => 'Дисциплина: ' . $form_state['storage']['cur_dis_name'] . '<br>Номер УП: ' . $form_state['storage']['cur_curriculum_num'] . '<br><br>',
				);
			
			if(!empty($form_state['storage']['options']))
			{
				$form['step2']['versions'] = array(
					'#type' => 'select', 
					'#title' => 'Выберите версию рабочей программы', 
					'#default_value' => $form_state['storage']['default_version'],
					'#options' => $form_state['storage']['options'],
					);

				$form['step2']['download'] = array(
					'#type' => 'image_button', 
					'#src' => '/sites/all/pic/download.png',
					);

				$form['step2']['preview'] = array(
					'#type' => 'image_button', 
					'#src' => '/sites/all/pic/preview.png',
					'#suffix' => '<br><br>'
					);

				$form['step2']['set_main'] = array(
					'#type' => 'submit', 
					'#value' => 'Сделать основной версией',
					'#suffix' => '<br><br>'
					);

				$form['step2']['replace'] = array(
					'#type' => 'submit', 
	            	'#value' => ' Заменить файл версии РП ',
					);	
			}
			

			$form['step2']['add_new'] = array(
				'#type' => 'submit', 
            	'#value' => 'Загрузить новую версию РП',
				);	

			$form['step2']['back'] = array(
				'#type' => 'submit', 
            	'#value' => 'Назад',
				);		

		break;

		case 3:

			$form['step3'] = array(
		        '#type' => 'fieldset',
		        '#collapsible' => TRUE, 
				'#collapsed' => FALSE, 
		        '#title' => 'Рабочие программы дисциплин',
		      	);

			$form['step3']['title'] = array(
				'#markup' => 'Дисциплина: ' . $form_state['storage']['cur_dis_name'] . '<br>Номер УП: ' . $form_state['storage']['cur_curriculum_num'] . '<br><br>',
				);

			$form['step3']['file_wp'] = array(
				'#title' => 'Выберите файл',
				'#name' => 'files[file_wp]',
		        '#type' => 'file',
		      	);

			$form['step3']['load_new'] = array(
					'#type' => 'submit', 
	            	'#value' => 'Загрузить',
					);

			$form['step3']['cancel'] = array(
					'#type' => 'submit', 
	            	'#value' => 'Отмена',
					);
		break;

		case 4:
			$form['step4'] = array(
		        '#type' => 'fieldset',
		        '#collapsible' => TRUE, 
				'#collapsed' => FALSE, 
		        '#title' => 'Рабочие программы дисциплин',
		      	);

			$form['step4']['title'] = array(
				'#markup' => 'Дисциплина: ' . $form_state['storage']['cur_dis_name'] . '<br>Номер УП: ' . $form_state['storage']['cur_curriculum_num'] . '<br><br>',
				);

			$form['step4']['file_wp'] = array(
				'#title' => 'Выберите файл',
				'#name' => 'files[file_replace]',
		        '#type' => 'file',
		      	);

			$form['step4']['replace_new'] = array(
					'#type' => 'submit', 
	            	'#value' => 'Заменить',
					);

			$form['step4']['cancel'] = array(
					'#type' => 'submit', 
	            	'#value' => 'Отмена',
					);
		break;	

	}

    return $form;
}

function personal_teacher_work_program_ajax_callback($form, &$form_state) 
{
    return $form;
}

function personal_teacher_work_program_form_submit($form, &$form_state)
{
	$current_step = 'step' . $form_state['storage']['step'];
    if (!empty($form_state['values'][$current_step])) 
    {
        $form_state['storage']['values'][$current_step] = $form_state['values'][$current_step];
    }

    if (isset($form['step1']['goto']['#value']) && $form_state['triggering_element']['#value'] == $form['step1']['goto']['#value']) 
    {

        $form_state['storage']['step']++;
        $rows = $form_state['storage']['rows'];
        $i = $form_state['values']['step1']['select_dis'];

        $form_state['storage']['cur_dis_name'] = $rows[$i]['dis_name'];
        $form_state['storage']['cur_curriculum_num'] = $rows[$i]['curriculum_num'];
        $form_state['storage']['cur_curriculum_dis_id'] = $rows[$i]['idCurriculumDiscipline'];

        $server = 'localhost';
		$username = 'moevm_user';
		$password = 'Pwt258E6JT8QAz3y';
		$database = 'moevmdb';
	  
	    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	    mysqli_query ($mysqli, "SET NAMES `utf8`");
	    $options = array();

		$work_program_result = mysqli_query ($mysqli, "SELECT * FROM workprogramversion
										WHERE `CurriculumDiscipline` = '" . $form_state['storage']['cur_curriculum_dis_id'] . "'");
		if($work_program_result)
		{
			$work_program = $work_program_result->fetch_assoc();
			if($work_program)
			{
				$i = 0;
				foreach ($work_program_result as $row) 
				{
					$options[$i] = basename($row['FileName']);
					if($row['CurrentVersion'] == 1)
					{
						$form_state['storage']['default_version'] = $i;
						$options[$i] .= ' (основная)';
					}

					$i++;
				}
			}
			$work_program_result->close();
		}

        $form_state['storage']['options'] = $options;

        $step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }
        $mysqli->close();

    }

    if (isset($form['step2']['add_new']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['add_new']['#value']) 
     {
     	$form_state['storage']['step']++;
     	$step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }
     }

    if(isset($form['step2']['replace']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['replace']['#value'])
    {
    	$form_state['storage']['step'] = 4;
    	$selected_wp = $form_state['values']['step2']['versions'];
    	$form_state['storage']['cur_version'] = $form_state['storage']['options'][$selected_wp];

     	$step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }
    }

    if (isset($form['step3']['load_new']['#value']) && $form_state['triggering_element']['#value'] == $form['step3']['load_new']['#value'])
    {
    	$file = $form_state['values']['file_wp'];
    	$file_path = $file->uri;

    	$server = 'localhost';
		$username = 'moevm_user';
		$password = 'Pwt258E6JT8QAz3y';
		$database = 'moevmdb';
	  
	    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	    mysqli_query ($mysqli, "SET NAMES `utf8`");

		$work_program_result = mysqli_query ($mysqli, "SELECT * FROM workprogramversion
										WHERE `CurriculumDiscipline` = '" . $form_state['storage']['cur_curriculum_dis_id'] . "'");

		if($work_program_result)
		{
			$work_program = $work_program_result->fetch_assoc();
			if($work_program)
			{
				mysqli_query ($mysqli, "INSERT INTO workprogramversion
										(`FileName`, `LastModificationDate`, `CurrentVersion`, `CurriculumDiscipline`)
										VALUES ('" . $file_path . "', '" . date('Y-m-d-H-i-s') . "',
										'" . 0 . "', '" . $form_state['storage']['cur_curriculum_dis_id'] . "')");
			}
			else
			{
				mysqli_query ($mysqli, "INSERT INTO workprogramversion
										(`FileName`, `LastModificationDate`, `CurrentVersion`, `CurriculumDiscipline`)
										VALUES ('" . $file_path . "', '" . date('Y-m-d-H-i-s') . "',
										'" . 1 . "', '" . $form_state['storage']['cur_curriculum_dis_id'] . "')");
			}
			$work_program_result->close();
		}
		else
		{
			mysqli_query ($mysqli, "INSERT INTO workprogramversion
										(`FileName`, `LastModificationDate`, `CurrentVersion`, `CurriculumDiscipline`)
										VALUES ('" . $file_path . "', '" . date('Y-m-d-H-i-s') . "',
										'" . 1 . "', '" . $form_state['storage']['cur_curriculum_dis_id'] . "')");
		}

		$file->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
		file_save($file); // Сохраняем новый статус

		$options = array();

		$work_program_result = mysqli_query ($mysqli, "SELECT * FROM workprogramversion
										WHERE `CurriculumDiscipline` = '" . $form_state['storage']['cur_curriculum_dis_id'] . "'");
		if($work_program_result)
		{
			$work_program = $work_program_result->fetch_assoc();
			if($work_program)
			{
				$i = 0;
				foreach ($work_program_result as $row) 
				{
					$options[$i] = basename($row['FileName']);
					if($row['CurrentVersion'] == 1)
					{
						$form_state['storage']['default_version'] = $i;
						$options[$i] .= ' (основная)';
					}

					$i++;
				}
			}
			$work_program_result->close();
		}

		$mysqli->close();

        $form_state['storage']['options'] = $options;

		$form_state['storage']['step']--;
     	$step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }

    }

    if (isset($form['step4']['replace_new']['#value']) && $form_state['triggering_element']['#value'] == $form['step4']['replace_new']['#value'])
    {
    	$file = $form_state['values']['file_replace'];
    	$file_path = $file->uri;
    	$selected_wp_name = $form_state['storage']['cur_version'];

    	$old_path = 'public://documents/work_programs/' . $form_state['storage']['cur_curriculum_dis_id'] . '/' . $selected_wp_name;

    	//debug($path);

    	$server = 'localhost';
		$username = 'moevm_user';
		$password = 'Pwt258E6JT8QAz3y';
		$database = 'moevmdb';
	  
	    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	    mysqli_query ($mysqli, "SET NAMES `utf8`");

		mysqli_query ($mysqli, "UPDATE workprogramversion SET
								 `FileName` = '" . $file_path . "',
								 `LastModificationDate` = '" . date('Y-m-d-H-i-s') . "'
										WHERE `FileName` = '" . $old_path . "'");

		$file->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
		file_save($file); // Сохраняем новый статус

		$options = array();

		$work_program_result = mysqli_query ($mysqli, "SELECT * FROM workprogramversion
										WHERE `CurriculumDiscipline` = '" . $form_state['storage']['cur_curriculum_dis_id'] . "'");
		if($work_program_result)
		{
			$work_program = $work_program_result->fetch_assoc();
			if($work_program)
			{
				$i = 0;
				foreach ($work_program_result as $row) 
				{
					$options[$i] = basename($row['FileName']);
					if($row['CurrentVersion'] == 1)
					{
						$form_state['storage']['default_version'] = $i;
						$options[$i] .= ' (основная)';
					}

					$i++;
				}
			}
			$work_program_result->close();
		}

		$mysqli->close();

        $form_state['storage']['options'] = $options;

		$form_state['storage']['step'] = 2;
     	$step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }

    }

    if (isset($form['step2']['download']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['download']['#value'])
    {
    	$selected_wp = $form_state['values']['step2']['versions'];
    	$selected_wp_name = $form_state['storage']['options'][$selected_wp];
    	$selected_wp_name = basename($selected_wp_name, ' (основная)');
    	$uri = 'public://documents/work_programs/' . $form_state['storage']['cur_curriculum_dis_id'] . '/' . $selected_wp_name;
    	$url = file_create_url($uri);

    	drupal_goto($url);
    }

	if (isset($form['step2']['preview']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['preview']['#value'])
    {
    	$selected_wp = $form_state['values']['step2']['versions'];
    	$selected_wp_name = $form_state['storage']['options'][$selected_wp];
    	$selected_wp_name = basename($selected_wp_name, ' (основная)');
    	$uri = 'public://documents/work_programs/' . $form_state['storage']['cur_curriculum_dis_id'] . '/' . $selected_wp_name;
    	$url = file_create_url($uri);

    	drupal_goto("http://docs.google.com/viewer?url=" . $url);
    }


    if (isset($form['step2']['back']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['back']['#value'])
    {
    	$form_state['storage']['step']--;
    	$step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }
    }

    if (isset($form['step3']['cancel']['#value']) && $form_state['triggering_element']['#value'] == $form['step3']['cancel']['#value'])
    {
    	$form_state['storage']['step']--;
    	$step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }
    }

    if (isset($form['step4']['cancel']['#value']) && $form_state['triggering_element']['#value'] == $form['step4']['cancel']['#value'])
    {
    	$form_state['storage']['step'] = 2;
    	$step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }
    }

    if (isset($form['step2']['set_main']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['set_main']['#value'])
    {
    	$selected_wp = $form_state['values']['step2']['versions'];
    	$selected_wp_name = $form_state['storage']['options'][$selected_wp];

    	if(strpos($selected_wp_name, '(основная)') === FALSE)
    	{
    		$uri = 'public://documents/work_programs/' . $form_state['storage']['cur_curriculum_dis_id'] . '/' . $selected_wp_name;

    		$server = 'localhost';
		    $username = 'moevm_user';
			$password = 'Pwt258E6JT8QAz3y';
			$database = 'moevmdb';
		  
		    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
		    mysqli_query ($mysqli, "SET NAMES `utf8`");

		   /* mysqli_query ($mysqli, "UPDATE workprogramversion SET
									 `CurrentVersion` = '0'
									 WHERE `CurrentVersion` = '1'");*/

			mysqli_query ($mysqli, "UPDATE workprogramversion SET
									 `CurrentVersion` = '1'
									 WHERE `FileName` = '" . $uri . "'");

			$work_program_result = mysqli_query ($mysqli, "SELECT * FROM workprogramversion
										WHERE `CurriculumDiscipline` = '" . $form_state['storage']['cur_curriculum_dis_id'] . "'");
			if($work_program_result)
			{
				$work_program = $work_program_result->fetch_assoc();
				if($work_program)
				{
					$i = 0;
					foreach ($work_program_result as $row) 
					{
						$options[$i] = basename($row['FileName']);
						if($row['CurrentVersion'] == 1)
						{
							$form_state['storage']['default_version'] = $i;
							$options[$i] .= ' (основная)';
						}

						$i++;
					}
				}
				$work_program_result->close();
			}

			$mysqli->close();

	        $form_state['storage']['options'] = $options;

    	}

    }

    $form_state['rebuild'] = TRUE;
}

function personal_teacher_work_program_form_validate($form, &$form_state)
{
	if (isset($form['step3']['load_new']['#value']) && $form_state['triggering_element']['#value'] == $form['step3']['load_new']['#value']) 
    {
        $validators = array(
            'file_validate_extensions' => array('doc docx odt'), // Проверка на расширения
        );

        $new_path = 'public://documents/work_programs/' . $form_state['storage']['cur_curriculum_dis_id'] . '/';
		file_prepare_directory($new_path, FILE_CREATE_DIRECTORY);
    	if ($file = file_save_upload('file_wp', $validators, $new_path, FILE_EXISTS_REPLACE)) 
	    {
	        if ($file) {
	  			$new_filename = date('Y-m-d-H-i-s') . '.' . pathinfo($file->filename, PATHINFO_EXTENSION);
	  			$file = file_move($file, $new_path . $new_filename);
	  			$form_state['values']['file_wp'] = $file; 
	  			//debug($file_one);
	  			//debug($form_state['storage']['file_one_copy']);
			}
	    }
	    else 
	    {
	        form_set_error('file_wp', 'Файл не был выбран');
	    }
    }

    if (isset($form['step4']['replace_new']['#value']) && $form_state['triggering_element']['#value'] == $form['step4']['replace_new']['#value'])
    {
    	 $validators = array(
            'file_validate_extensions' => array('doc docx odt'), // Проверка на расширения
        );

        $new_path = 'public://documents/work_programs/' . $form_state['storage']['cur_curriculum_dis_id'] . '/';
		file_prepare_directory($new_path, FILE_CREATE_DIRECTORY);
    	if ($file = file_save_upload('file_replace', $validators, $new_path, FILE_EXISTS_REPLACE)) 
	    {
	        if ($file) 
	        {
	  			$new_filename = date('Y-m-d-H-i-s') . '.' . pathinfo($file->filename, PATHINFO_EXTENSION);
	  			$file = file_move($file, $new_path . $new_filename);
	  			$form_state['values']['file_replace'] = $file; 
	  			//debug($file_one);
	  			//debug($form_state['storage']['file_one_copy']);
			}
    	}
    	else 
	    {
	        form_set_error('file_replace', 'Файл не был выбран');
	    }
    }
  
}