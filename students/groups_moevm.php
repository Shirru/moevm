<?php
include 'groups_moevm_add_group.php';

function groups_moevm_form($form, &$form_state)
{
	global $user;
	$is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$groups = groups_moevm_all_groups();
	$table = groups_moevm_split_groups($groups);

	$form['bachelors'] = array(
		'#type' => 'fieldset',
		'#title' => t('Бакалавры'),
		'#collapsible' => true,   
	    '#collapsed'  => false, 
		);

	$form['bachelors']['groups'] = array(
		'#markup' => $table[0],
		);

	$form['masters'] = array(
		'#type' => 'fieldset',
		'#title' => t('Магистры'),
		'#collapsible' => true,   
	    '#collapsed'  => false, 
		);

	$form['masters']['groups'] = array(
		'#markup' => $table[1],
		);

	if(!$is_denied)
	{
		$form['add_new'] = array(
			'#type' => 'submit',
			'#value' => 'Добавить группу',
			);
	}

	return $form;
}

function groups_moevm_all_groups()
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

  	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
  	mysqli_query ($mysqli, "SET NAMES `utf8`");

  	$groups_result = $mysqli->query("SELECT * FROM `group`
  	 	WHERE `curriculum` IN 
  	 		(SELECT `idCurriculum` FROM `curriculum`
         			WHERE `Chair` = (SELECT `idChair` FROM `chair` 
                         WHERE `ChairNum` = '14'))");

  	$groups = array();
	$i = 0;
	foreach ($groups_result as $row) 
	{
		$groups[$i]['id'] = $row['idGroup'];
		$groups[$i]['group_num'] = $row['GroupNum'];
		$groups[$i]['size'] = $row['Size'];

		if($row['Head'])
		{
			$head_result = $mysqli->query("SELECT `Surname`, `FirstName`
                 FROM student
                 WHERE `idStudent` = '" . $row['Head'] . "'");

			if(!empty($head_result))
			{
				$head = $head_result->fetch_assoc();
				$groups[$i]['head'] = $head['Surname'] . ' ' . $head['FirstName'];
				$head_result->close();
			}
			else
			{
				$groups[$i]['head'] = '';
			}
		}
		else
		{
			$groups[$i]['head'] = '';
		}
		

		$groups[$i]['head_email'] = $row['E-mail'];

		if($row['Curriculum'])
		{
			$curriculum_result = $mysqli->query("SELECT `CurriculumNum`, `Stage`, `Direction`
                 FROM `curriculum`
                 WHERE `idCurriculum` = '" . $row['Curriculum'] . "'");

			if(!empty($curriculum_result))
			{
				$curriculum = $curriculum_result->fetch_assoc();
				$groups[$i]['curriculum_num'] = $curriculum['CurriculumNum'];
				$groups[$i]['stage'] = $curriculum['Stage'];

				$direction_result = $mysqli->query("SELECT `DirectionName`
                 FROM `direction`
                 WHERE `idDirection` = '" . $curriculum['Direction'] . "'");

				if(!empty($direction_result))
				{
					$direction = $direction_result->fetch_assoc();
					$groups[$i]['direction'] = $direction['DirectionName'];
					$direction_result->close();
				}

				$curriculum_result->close();
			}
			else
			{
				$groups[$i]['curriculum_num'] = '';
				$groups[$i]['stage'] = '';
				$groups[$i]['direction'] = '';
			}
		}
		else
		{
			$groups[$i]['curriculum_num'] = '';
			$groups[$i]['stage'] = '';
			$groups[$i]['direction'] = '';
		}
		

		$creation_year = $row['CreationYear'];
		if(date('n') >= 9)
			$groups[$i]['course'] = date('Y') - $creation_year + 1;
		else
			$groups[$i]['course'] = date('Y') - $creation_year;

		$i++;
	}

	$mysqli->close();

	return $groups;
}

function groups_moevm_split_groups($groups)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$bachelor_directions = array();
	$master_directions = array();
	$bachelor_groups = array();
	$master_groups = array();

  	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
  	mysqli_query ($mysqli, "SET NAMES `utf8`");

	// Список всех направлений бакалавров
  	$bachelor_directions_result =  mysqli_query ($mysqli, "SELECT `DirectionName`
  		FROM `direction`
  		WHERE `idDirection` IN
  		(SELECT `Direction` FROM `curriculum`
  		WHERE `Stage` = 1)");

  	foreach($bachelor_directions_result as $row)
  	{
  		$bachelor_directions[] = $row['DirectionName'];
  	}
  	$bachelor_directions_result->close();

  	//Список всех направлений магистров
  	$master_directions_result =  mysqli_query ($mysqli, "SELECT `DirectionName`
  		FROM `direction`
  		WHERE `idDirection` IN
  		(SELECT `Direction` FROM `curriculum`
  		WHERE `Stage` = 2)");

  	foreach($master_directions_result as $row)
  	{
  		$master_directions[] = $row['DirectionName'];
  	}
  	$master_directions_result->close();

  	// Разделяем группы по направлениям и стадиям обучения (бакалавр/магистр)
  	for($i = 0; $i < count($groups); $i++)
  	{
  		if($groups[$i]['stage'] == 1 && $groups[$i]['course'] <= 4) // если группа бакалавров
  		{
  			for($j = 0; $j < count($bachelor_directions); $j++)
  			{
  				if($groups[$i]['direction'] == $bachelor_directions[$j])
  				{
  					$bachelor_groups[$j][] = $groups[$i];
  				}
  			}
  		}
  		else if($groups[$i]['stage'] == 2)
  		{
  			for($j = 0; $j < count($master_directions); $j++)
  			{
  				if($groups[$i]['direction'] == $master_directions[$j])
  				{
  					$master_groups[$j][] = $groups[$i];
  				}
  			}
  		}
  	}

  	$output = array(
  		0 => '',
  		1 => ''
  		);

  	for($i = 0; $i < count($bachelor_groups); $i++)
  	{
  		$output[0] .= '<h3>' . $bachelor_directions[$i] . '</h3>';
  		$output[0] .=  groups_moevm_get_table($bachelor_groups[$i]);
  	}

  	for($i = 0; $i < count($master_groups); $i++)
  	{
  		$output[1] .= '<h3>' . $master_directions[$i] . '</h3>';
  		$output[1] .=  groups_moevm_get_table($master_groups[$i]);
  	}

  	return $output;
}

function groups_moevm_get_table($rows)
{
	global $user;
	$is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

    if($is_denied)
    {
    	$header = array('', 'Группа', 'Численность', 'Староста', 'E-mail', 'Учебный план', 'Курс');
    }
    else
    {
    	$header = array('', 'Группа', 'Численность', 'Староста', 'E-mail', 'Учебный план', 'Курс', '');
    }
	
	$table_rows = array();

	foreach ($rows as $row)
 	{
 		if($is_denied)
 		{
 			$table_rows[] = array(
				"<a href='moevm/view?id=" . $row["id"] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
				$row['group_num'],
				$row['size'],
				$row['head'],
				$row['head_email'],
				$row['curriculum_num'],
				$row['course'],
				);
 		}
 		else
 		{
 			$table_rows[] = array(
				"<a href='moevm/view?id=" . $row["id"] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
				$row['group_num'],
				$row['size'],
				$row['head'],
				$row['head_email'],
				$row['curriculum_num'],
				$row['course'],
				"<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить группу?\")){parent.location = \"del?dismo=true&id=" . $row ["id"] . "\";}else return false;'  title='удаление'><img src='/sites/all/pic/delete.png'></a>"
				);
 		}
		
	}

	return theme('table', array('header' => $header, 'rows' => $table_rows));
}

function groups_moevm_form_submit($form, &$form_state)
{
	drupal_goto('groups/moevm/add_group');
}