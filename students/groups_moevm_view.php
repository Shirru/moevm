<?php

function groups_moevm_view_form($form, &$form_state)
{
	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$group_id = (isset($form_state['storage']['group_id'])) ? $form_state['storage']['group_id'] : $_GET['id']; 
	$form_state['storage']['group_id'] = $group_id;
	$group = get_group_by_id($group_id);
	$curriculum_nums = get_curriculum_nums();
	$students = get_students($group_id);
	$students_options = array();
	$default_value_head = 0;
	$students_rows = array();

	for($i = 0; $i < count($curriculum_nums); $i++)
	{
		if($curriculum_nums[$i] == $group['CurriculumNum'])
			$default_value = $i;
	}

	$years = range(date('Y') - 10, date('Y') + 10);

	for($i = 0; $i < count($years); $i++)
	{
		if($years[$i] == $group['CreationYear'])
			$default_value_year = $i;
	}

	$students_options[] = 'Выберите старосту';
	for($i = 0; $i < count($students); $i++)
	{
		if($students[$i]['idStudent'] == $group['Head'])
			$default_value_head = $i+1;

		$students_options[] = $students[$i]['Surname'] . ' ' . $students[$i]['FirstName'] .
		 ' (' . $students[$i]['RecordBookNum'] . ')';

		if($is_denied)
		{
			$students_rows[] = array(
				"<a href='student?id=" . $students[$i]["idStudent"] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
				$i+1,
				$students[$i]['RecordBookNum'],
				$students[$i]['Surname'],
				$students[$i]['FirstName'],
				$students[$i]['Patronymic'],
				); 
		}
		else
		{
			$students_rows[] = array(
				"<a href='student?id=" . $students[$i]["idStudent"] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
				$i+1,
				$students[$i]['RecordBookNum'],
				$students[$i]['Surname'],
				$students[$i]['FirstName'],
				$students[$i]['Patronymic'],
				"<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить студента?\")){parent.location = \"/groups/del?student_id=" . $students[$i]["idStudent"] . "&group_id=" . $group_id . "\";}else return false;'  title='удаление'><img src='/sites/all/pic/delete.png'></a>"
				); 
		}
	}

	if($is_denied)
	{
		$readonly = 'readonly';
		$header = array('', '№', 'Номер зачетной книжки', 'Фамилия', 'Имя', 'Отчество');
	}
	else 
	{
		$header = array('', '№', 'Номер зачетной книжки', 'Фамилия', 'Имя', 'Отчество', '');
		$readonly = '';
	}

	$form['column_left'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-left'),
			'style' => array('float: left'),
			),
		);
	
	$form['column_left']['number'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер группы',
		'#size' => 30,
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $group['GroupNum'],
		);

	$form['column_left']['curriculum'] = array(
		'#type' => 'select',
		'#title' => 'Номер УП',
		'#options' => $curriculum_nums,
		'#default_value' => $default_value,
		);

	$form['column_left']['head'] = array(
		'#type' => 'select',
		'#title' => 'Староста',
		'#options' => $students_options,
		'#default_value' => $default_value_head,
		);

	$form['column_right'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-right'),
			'style' => array('float: right'),
			),
		);

	$form['column_right']['creation_year'] = array(
		'#type' => 'select',
		'#title' => 'Год создания',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#options' => $years,
		'#default_value' => $default_value_year,
		);

	$form['column_right']['size'] = array(
		'#type' => 'textfield',
		'#title' => 'Численность',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 30,
		'#default_value' => $group['Size'],
		);

	$form['column_right']['Email'] = array(
		'#type' => 'textfield',
		'#title' => 'E-mail старосты/группы',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 30,
		'#default_value' => $group['E-mail'],
		);

	if(!$is_denied)
	{
		$form['column_left']['save'] = array(
		'#type' => 'submit',
		'#value' => 'Сохранить',
		);

		$form['students'] = array(
			'#type' => 'fieldset',
			'#title' => 'Список студентов',
			'#collapsible' => true,
			'#collapsed' => true,
			'#attributes' => array(
				'style' => array('clear: left;'),
				),
			);
	}
	else
	{
		$form['students'] = array(
			'#type' => 'fieldset',
			'#title' => 'Список студентов',
			'#collapsible' => true,
			'#collapsed' => true,
			'#attributes' => array(
				'style' => array('clear: right;'),
				),
			);
	}


	$form['students']['table'] = array(
		'#markup' => theme('table', array('header' => $header, 'rows' => $students_rows)),
		);

	if(!$is_denied)
	{
		$form['students']['add_student'] = array(
			'#type' => 'submit',
			'#value' => 'Добавить студента',
			);

		$form['students']['load'] = array(
			'#type' => 'fieldset',
			'#title' => 'Загрузка списка студентов из файла',
			'#collapsible' => true,
			'#collapsed' => false,
			'#prefix' => '<div id = "load-students-result-wrapper">',
			'#suffix' => '</div>'
			);

		$form['students']['load']['notice'] = array(
			'#markup' => '<b><i>Примечание: </b>если уже есть данные о студентах группы, то они будут удалены.</i>'
			);

		$form['students']['load']['file'] = array(
			'#type' => 'file',
			'#name' => 'files[list]',
			);

		$form['students']['load']['load_students'] = array(
			'#type' => 'submit',
			'#value' => 'Загрузить список студентов',
			'#ajax' => array(
		        'wrapper' => 'load-students-result-wrapper', 
		        'callback' => 'groups_moevm_view_ajax_callback',
		        ),
			);

		if(!empty($form_state['storage']['result']))
		{
			$form['students']['load']['table'] = array(
				'#markup' => $form_state['storage']['result'],
				);

			$form['students']['load']['cancel'] = array(
				'#type' => 'submit',
				'#value' => 'Отмена',
				);

			$form['students']['load']['update_students'] = array(
				'#type' => 'submit',
				'#value' => 'Сохранить список',
				);
		}
	}
	
	return $form;
}

function groups_moevm_view_ajax_callback($form, &$form_state)
{
	return $form['students']['load'];
}

function groups_moevm_view_form_validate($form, &$form_state) 
{
	if (isset($form['students']['load']['load_students']['#value']) && $form_state['triggering_element']['#value'] == $form['students']['load']['load_students']['#value']) 
	{
		if (empty($form_state['values']['number']) || !is_numeric($form_state['values']['number'])) 
		{
	    	form_set_error('number', t('Некорректное значение поля, введите номер группы'));
	  	}
		
		$validators = array(
	        'file_validate_extensions' => array('xlsx xls xlsm'), // Проверка на расширения
	    	);

	    if ($file = file_save_upload('list', $validators, 'public://documents/')) 
	    {

		  	$new_filename = date('YmdHis') . '.' . pathinfo($file->filename, PATHINFO_EXTENSION);
		  	$file = file_move($file, 'public://documents/' . $new_filename);  		

	        $form_state['values']['list'] = $file; 
	    }
	    else 
	    {
	        form_set_error('list', 'Файл не был загружен');
	    }

	}
}

function groups_moevm_view_form_submit($form, &$form_state)
{
	$group_id = $form_state['storage']['group_id']; 
	if (isset($form['column_left']['save']['#value']) && $form_state['triggering_element']['#value'] == $form['column_left']['save']['#value']) 
	{
		$curriculum_num = $form_state['complete form']['column_left']['curriculum']['#options'][$form_state['values']['curriculum']];
		$head = $form_state['complete form']['column_left']['head']['#options'][$form_state['values']['head']];
		$year = $form_state['complete form']['column_right']['creation_year']['#options'][$form_state['values']['creation_year']];

		save_corrected_group_to_db($group_id, $form_state['values']['number'], $curriculum_num, $head, $year, $form_state['values']['size'], $form_state['values']['Email']);
	}

	if (isset($form['students']['load']['load_students']['#value']) && $form_state['triggering_element']['#value'] == $form['students']['load']['load_students']['#value']) 
	{
		require_once libraries_get_path('Classes') . "/PHPExcel.php";

	    $path = 'public://documents/';
	    $path = drupal_realpath($path);

	    $file = $form_state['values']['list'];

	    $excel = PHPExcel_IOFactory::load($path . "/" . $file->filename);
	    $students = parse_students($excel, $form_state['values']['number']);

	    $header = array('Фамилия', 'Имя', 'Отчество', 'Номер зачётной книжки');
	    $form_state['storage']['result'] = theme('table', array('header' => $header, 'rows' => $students));
	    $form_state['storage']['students'] = $students;

	    file_delete($file);
	}

	if (isset($form['students']['load']['update_students']['#value']) && $form_state['triggering_element']['#value'] == $form['students']['load']['update_students']['#value'])
	{
		$students = $form_state['storage']['students'];
	
		if(!empty($form_state['storage']['students']))
		{
			delete_students_from_db($group_id);
			save_students_in_db($form_state['storage']['students'], $form_state['values']['number']);
			$form_state['storage']['students'] = NULL;
			$form_state['storage']['result'] = NULL;
		}

	}

	if (isset($form['students']['load']['cancel']['#value']) && $form_state['triggering_element']['#value'] == $form['students']['load']['cancel']['#value'])
	{
		$form_state['storage']['students'] = NULL;
		$form_state['storage']['result'] = NULL;
	}

	if (isset($form['students']['add_student']['#value']) && $form_state['triggering_element']['#value'] == $form['students']['add_student']['#value'])
	{
		drupal_goto('groups/moevm/add_student', array(
			'query' => array('id'=>$group_id,)));
	}

	$form_state['rebuild'] = TRUE;
}

function save_corrected_group_to_db($id, $num, $curriculum_num, $head, $year, $size, $email)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $curriculum_result = mysqli_query ($mysqli, "SELECT `idCurriculum`
    	FROM `curriculum`
    	WHERE `CurriculumNum` = '" . $curriculum_num . "'");

    $id_curriculum = $curriculum_result->fetch_assoc();
    $curriculum_result->close();

    if($head != 'Выберите старосту')
    {
    	$record_book = substr ($head , strpos($head, $num) , 6 );

	    $head_result = mysqli_query ($mysqli, "SELECT `idStudent`
	    	FROM `student`
	    	WHERE `RecordBookNum` = '" . $record_book . "'");

	    $h = $head_result->fetch_assoc();
	    $id_head = $h['idStudent'];
	    $head_result->close();

 	$is_success = mysqli_query ($mysqli, "UPDATE `group`
    	SET `GroupNum` = '" . $num . "',
    	`Curriculum` = '" . $id_curriculum['idCurriculum'] . "', 
    	`Head` = '" . $id_head . "',
    	`CreationYear` = '" . $year . "',
    	`Size` = '" . $size . "',
    	`E-mail` = '" . $email . "'
    	WHERE `idGroup` = '" . $id . "'");
    }
    else
	{
		$id_head = '';
		 $is_success = mysqli_query ($mysqli, "UPDATE `group`
    		SET `GroupNum` = '" . $num . "',
    		`Curriculum` = '" . $id_curriculum['idCurriculum'] . "', 
    		`CreationYear` = '" . $year . "',
    		`Size` = '" . $size . "',
    		`E-mail` = '" . $email . "'
    		WHERE `idGroup` = '" . $id . "'");
	}

    
    if($is_success)
    	drupal_set_message('Данные обновлены успешно!');
    else
    	drupal_set_message('Ошибка при обновлении данных', 'error');
}

function get_group_by_id($group_id)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT *
    	FROM `group`
    	WHERE `idGroup` = '" . $group_id . "'");

    $group = $group_result->fetch_assoc();
    $group_result->close();

    $curriculum_result = mysqli_query ($mysqli, "SELECT `CurriculumNum`
    	FROM `curriculum`
    	WHERE `idCurriculum` = '" . $group['Curriculum'] . "'");

    $curriculum = $curriculum_result->fetch_assoc();
    $curriculum_result->close();

    $mysqli->close();

    $group['CurriculumNum'] = $curriculum['CurriculumNum'];

    return $group;
}

function get_students($group_id)
{
	$students = array();
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT *
    	FROM `student`
    	WHERE `Group` = '" . $group_id . "'");

    foreach ($group_result as $student) 
    {
    	$students[] = $student;
    }

    $group_result->close();
    $mysqli->close();

    return $students;
}

function delete_students_from_db($group_id)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $is_success = mysqli_query ($mysqli, "DELETE FROM `student`
    		WHERE `Group` = '" . $group_id . "'");
    
    $mysqli->close();
}
