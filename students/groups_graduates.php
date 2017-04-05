<?php

function groups_graduates_page()
{
	$groups = groups_moevm_all_groups();
	$rows_bachelors = array();
	$rows_masters = array();
	$output ='';

	for ($i = 0; $i < count($groups); $i++) 
	{ 
		if($groups[$i]['course'] == 4)
			$rows_bachelors[] = $groups[$i];
		if($groups[$i]['course'] == 6)
			$rows_masters[] = $groups[$i];
	}

	if(!empty($rows_bachelors))
	{
		$output .= '<h3>Бакалавры</h3>';
		$output .= groups_moevm_get_table_without_links($rows_bachelors);
		$output .= '<br><br>';
	}

	if(!empty($rows_masters))
	{
		$output .= '<h3>Магистры</h3>';
		$output .= groups_moevm_get_table_without_links($rows_masters);
	}
	
	return $output;
}

function groups_moevm_get_table_without_links($rows)
{
	$header = array('','Группа', 'Численность', 'Староста', 'E-mail', 'Учебный план', 'Курс');
	$table_rows = array();

	foreach ($rows as $row)
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

	return theme('table', array('header' => $header, 'rows' => $table_rows));
}