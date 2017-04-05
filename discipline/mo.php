<?php
/**
 * Загрузка группы
 */

function discipline_moevm_page() {
	/*$output = discipline_moevm_page_table();
    $output .= render(discipline_moevm_form());
//	$form1 = drupal_get_form('disciplines_moevm_page_form');
//	$output .= render($form1);
//	$form2 = drupal_get_form('mymodule_ex2_form');
//	$output = render($form2);
	return $output;*/

    return drupal_get_form ( 'discipline_moevm_form' );
}

function discipline_moevm_form($form, &$form_state) {
    global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

    $disciplines = discipline_moevm_get_all_disciplines();
    $filters = array("Все дисциплины кафедры", "Дисциплины бакалавров", "Дисциплины магистров",
		"По направлениям");

    $form = array();

    $choice = isset($form_state['values']['filter_select']) ? $form_state['values']['filter_select'] : 0;

    $form['filter_select'] = array(
        '#title' => t('Выберите фильтр'),
        '#type' => 'select',
        '#options' => $filters,
        '#default_value' => 0,
        '#ajax' => array(
            'callback' => 'discipline_moevm_form_ajax_callback',
            'wrapper' => 'disciplines-div',
            'event' => 'change',
        ),
    );

    if(!$is_denied)
		$form['submit'] = array(
			'#type' => 'submit',
			'#value' => t('Новая дисциплина'),
		);

    $form['disciplines_block'] = array (
        '#prefix' => '<div id = "disciplines-div">',
        '#suffix' => '</div>',
	);

    $form['disciplines_block']['disciplines'] = array(
        '#markup' => discipline_moevm_get_table($choice, $disciplines),
	);

    return $form;
}

function discipline_moevm_form_ajax_callback($form, &$form_state) {
    return $form['disciplines_block'];
}

function discipline_moevm_form_submit($form, &$form_state) {
    drupal_goto("/disciplines/moevm/add");
}

function discipline_moevm_get_all_disciplines() {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb';

    $disciplines = array();

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $discipline_result = $mysqli->query("SELECT DISTINCT a.*, cur.Stage, dir.DirectionCode, dir.DirectionName
					FROM
						(SELECT dis.*, cd.Discipline, cd.Curriculum
						FROM discipline dis
						LEFT OUTER JOIN curriculumdiscipline cd ON dis.idDiscipline = cd.Discipline) a
					LEFT OUTER JOIN curriculum cur ON a.Curriculum = cur.idCurriculum
					LEFT OUTER JOIN direction dir ON cur.Direction = dir.idDirection
					WHERE a.Chair = (SELECT idChair FROM chair WHERE ChairNum = 14) ORDER BY a.DisFullName");

    if($discipline_result) {
        while ($row = $discipline_result -> fetch_assoc()) {
            $disciplines[] = $row + array('Directions' => "");
        }
        $discipline_result -> close();
	}

	for ($i = 0; $i < sizeof($disciplines); $i++) {
    	$direction_result = $mysqli->query("SELECT b.DirectionCode
							FROM
								(SELECT DISTINCT a.*, cur.Stage, dir.DirectionCode, dir.DirectionName
								FROM
									(SELECT dis.*, cd.Discipline, cd.Curriculum
									FROM discipline dis
								LEFT OUTER JOIN curriculumdiscipline cd ON dis.idDiscipline = cd.Discipline) a
								LEFT OUTER JOIN curriculum cur ON a.Curriculum = cur.idCurriculum
								LEFT OUTER JOIN direction dir ON cur.Direction = dir.idDirection
								WHERE a.Chair = (SELECT idChair FROM chair WHERE ChairNum = 14)) b
							WHERE b.idDiscipline = " . $disciplines[$i]['idDiscipline']);

        if($direction_result) {
            while ($row = $direction_result -> fetch_assoc()) {
                $disciplines[$i]['Directions'] .= $row['DirectionCode'] . "<br>";
            }
            $direction_result -> close();
        }

	}
	// dsm($disciplines);
    mysqli_close($mysqli);

    //dsm($disciplines);

    return $disciplines;
}

function discipline_moevm_get_table($type, $disciplines) {
	$table = '';

	switch ($type){
		case 0:
            $table = discipline_moevm_create_table($disciplines);
            break;
		case 1:
            //stage of education: 1 - bachelors, 2 - masters
            $table = discipline_moevm_table_stage(1, $disciplines);
            break;
		case 2:
			//stage of education: 1 - bachelors, 2 - masters
			$table = discipline_moevm_table_stage(2, $disciplines);
			break;
        case 3:
            //on directions
            $table = discipline_moevm_table_direction($disciplines);
            break;
	}

	return $table;
}

function discipline_moevm_create_table($disciplines) {
	$table = "";

    global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);

    if(($is_teacher || $is_student) && !$is_educational)
    {
        $header = array('', 'Полное название', 'Краткое название', 'Читается для направлений');
        $rows = array();

        foreach($disciplines as $row) {
            $rows[] = array("<a href='moevm/view?dis=".$row ["idDiscipline"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
                $row["DisFullName"],
                $row["DisShortName"],
				$row["Directions"],
            );
        }
    }
    else
    {
        $header = array('', 'Полное название', 'Краткое название', 'Читается для направлений', '');
        $rows = array();

        foreach($disciplines as $row) {
            $rows[] = array("<a href='moevm/view?dis=".$row ["idDiscipline"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
                $row["DisFullName"],
				$row["DisShortName"],
                $row["Directions"],
                "<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить дисциплину?\")){parent.location = \"del?dismo=true&dis_id=" . $row ["idDiscipline"] . "\";}else return false;'  title='удаление'><img src='/sites/all/pic/delete.png'></a>");
        }
    }

    $size = sizeof($rows);
    for($i = 1; $i < $size; $i++) {
    	if ($rows[$i-1][1] == $rows[$i][1]) {
            unset($rows[$i-1]);
		}
	}

    $table .= theme('table', array('header' => $header, 'rows' => $rows));

	return $table;
}

function discipline_moevm_table_stage($stage, $disciplines) {
	$data = array();

	foreach ($disciplines as $discipline) {
		if ($discipline["Stage"] == $stage)
			array_push($data, $discipline);
	}
	//dsm ($data);
    return discipline_moevm_create_table($data);
}

function discipline_moevm_table_direction($disciplines) {
    foreach ($disciplines as $key => $row) {
        $direction_code[$key]  = $row['DirectionCode'];
    }
    array_multisort($direction_code, SORT_ASC, $disciplines);

    $tables = "";

    $direction = $disciplines[0]["DirectionCode"];
    $direction_name = $disciplines[0]["DirectionName"];

    for ($i = 0; $i < sizeof($disciplines); ) {
        $tables .= "<h3>". $direction . " " . $direction_name ."</h3>";
        $data = array();
        while ($i < sizeof($disciplines) && $disciplines[$i]["DirectionCode"] == $direction) {
            array_push($data, $disciplines[$i]);
            $i++;
        }

        $dis_name = array();
        foreach ($data as $key => $row) {
            $dis_name[$key]  = $row['DisFullName'];
        }
        array_multisort($dis_name, SORT_ASC, $data);

        $tables .= discipline_moevm_create_table($data);

		if ($i < sizeof($disciplines)) {
			$direction = $disciplines[$i]["DirectionCode"];
            $direction_name = $disciplines[$i]["DirectionName"];
        }
	}

    return $tables;
}

?>
