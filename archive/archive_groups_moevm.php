<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 23.05.2017
 * Time: 3:38
 */

function archive_groups_moevm_page() {

    $year = $_GET['year'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $groups = archive_groups_moevm_all_groups($year);
    $table = archive_groups_moevm_split_groups($groups, $year);

    $output .= "<h3><b>Бакалавры</b></h3>" . $table[0];
    $output .= "<h3><b>Магистры</b></h3>" . $table[1];

    return $output;
}

function archive_groups_moevm_all_groups($year)
{
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

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

function archive_groups_moevm_split_groups($groups, $year)
{
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;
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
        $output[0] .=  archive_groups_moevm_get_table($bachelor_groups[$i], $year);
    }

    for($i = 0; $i < count($master_groups); $i++)
    {
        $output[1] .= '<h3>' . $master_directions[$i] . '</h3>';
        $output[1] .=  archive_groups_moevm_get_table($master_groups[$i], $year);
    }

    return $output;
}

function archive_groups_moevm_get_table($rows, $year)
{

    $header = array('', 'Группа', 'Численность', 'Староста', 'E-mail', 'Учебный план', 'Курс');

    $table_rows = array();

    foreach ($rows as $row)
    {

        $table_rows[] = array(
            "<a href='moevm/view?id=" . $row["id"] . "&year=" . $year . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row['group_num'],
            $row['size'],
            $row['head'],
            $row['head_email'],
            $row['curriculum_num'],
            $row['course'],
        );

    }

    return theme('table', array('header' => $header, 'rows' => $table_rows));
}