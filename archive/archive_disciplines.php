<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 22.05.2017
 * Time: 1:42
 */

function archive_disciplines_page()
{
    return drupal_get_form ( 'archive_disciplines_form' );
}

function archive_disciplines_form($form, &$form_state) {
    $year = $_GET['year'];

    $filters = array("Все дисциплины кафедры", "Дисциплины бакалавров", "Дисциплины магистров",
        "По направлениям");

    $form = array();

    $choice = isset($form_state['values']['filter_select']) ? $form_state['values']['filter_select'] : 0;

    if (isset($form_state['storage']['disciplines'])) {
        $disciplines = $form_state['storage']['disciplines'];
    }
    else {
        $disciplines = archive_disciplines_get_all_disciplines($year);
        $form_state['storage']['disciplines'] = $disciplines;
    }

    $form['filter_select'] = array(
        '#prefix' => "<h2>Данные за " . $year . " год</h2><br>",
        '#title' => t('Выберите фильтр'),
        '#type' => 'select',
        '#options' => $filters,
        '#default_value' => 0,
        '#ajax' => array(
            'callback' => 'archive_disciplines_form_ajax_callback',
            'wrapper' => 'disciplines-div',
            'event' => 'change',
        ),
    );

    $form['disciplines_block'] = array (
        '#prefix' => '<div id = "disciplines-div">',
        '#suffix' => '</div>',
    );

    $form['disciplines_block']['disciplines'] = array(
        '#markup' => archive_disciplines_get_table($choice, $disciplines, $year),
    );

    return $form;

}

function archive_disciplines_form_ajax_callback($form, &$form_state) {
    return $form['disciplines_block'];
}

function archive_disciplines_get_all_disciplines($year) {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive';

    $disciplines = array();

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $discipline_result = $mysqli->query("SELECT DISTINCT a.*, cur.Stage, dir.DirectionCode, dir.DirectionName
					FROM
						(SELECT dis.*, cd.Discipline, cd.Curriculum
						FROM discipline dis
						LEFT OUTER JOIN curriculumdiscipline cd ON dis.idDiscipline = cd.Discipline 
						WHERE dis.Year = " . $year . ") a
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

function archive_disciplines_get_table($type, $disciplines, $year) {
    $table = '';

    switch ($type){
        case 0:
            $table = archive_disciplines_create_table($disciplines, $year);
            break;
        case 1:
            //stage of education: 1 - bachelors, 2 - masters
            $table = archive_disciplines_table_stage(1, $disciplines, $year);
            break;
        case 2:
            //stage of education: 1 - bachelors, 2 - masters
            $table = archive_disciplines_table_stage(2, $disciplines, $year);
            break;
        case 3:
            //on directions
            $table = archive_disciplines_table_direction($disciplines);
            break;
    }

    return $table;
}

function archive_disciplines_create_table($disciplines, $year) {
    $table = "";

    $header = array('', 'Полное название', 'Краткое название', 'Читается для направлений');
    $rows = array();

    foreach($disciplines as $row) {
        $rows[] = array("<a href='disciplines/view?dis=".$row ["idDiscipline"]."&year=" . $year
            . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["DisFullName"],
            $row["DisShortName"],
            $row["Directions"],
        );
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

function archive_disciplines_table_stage($stage, $disciplines, $year) {
    $data = array();

    foreach ($disciplines as $discipline) {
        if ($discipline["Stage"] == $stage)
            array_push($data, $discipline);
    }
    //dsm ($data);
    return archive_disciplines_create_table($data, $year);
}

function archive_disciplines_table_direction($disciplines) {
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
