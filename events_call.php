<?php
	$GR_ID = '435'; 
	
    mysql_connect('localhost', 'cold_fusion', 'f1imFl4m') or die ('Unable to connect to the database');
    mysql_select_db('mysql_web_db') or die ('Unable to select database!');

	function check_values($array)
	//Checks if all values in an array are identical.  Returns TRUE or FALSE.  Why this isn't this built into PHP?
		{
			$num_elements = count($array);
			$check = $array[0];
			for($n = 1; $n < $num_elements; $n++)
				{
					if($array[$n] != $check)
						{
							return FALSE;
						}
				};
			return TRUE;
		};

	function format_times($start,$end)
	//This does a check to see if $start and $end are in the same half of the day and formats accordingly.
		{
			$strip_these = array('a.m.','p.m.');
			if($start == $end)
				{
					echo $start;
				}
			elseif(substr($start,-4) == substr($end,-4))
				{
					echo str_replace($strip_these,'',$start) . ' &ndash; ' . $end;
				}
			else
				{
					echo $start . ' &ndash; ' . $end;
				};
		};

	function get_times($event_id,$found_instances,$instancecount)
		{
			global $events;
			$fix_these = array('AM','PM','12:00 p.m.','12:00 a.m.',':00');
			$fixes = array('a.m.','p.m.','Noon','Midnight','');
			$i = 0;

			foreach($found_instances as $in_id)
			//Build a (temporary) array of time info that we can make some easy logic checks on.
				{
					$day[] = date("l",$events[$event_id][$in_id]['start']);
					$start_temp = date("g:i A",$events[$event_id][$in_id]['start']);
					$start[] = str_replace($fix_these,$fixes,$start_temp);
					$end_temp = date("g:i A",$events[$event_id][$in_id]['end_dt']);
					$end[] = str_replace($fix_these,$fixes,$end_temp);
					$date[] = date("l, F, j, Y",$events[$event_id][$in_id]['start']);
				};

			if($instancecount > 1) // Multiple instances of one event
				{
					if($events[$event_id]['display_date'] and (check_values($start) and check_values($end)))
					//Display "display date" only if there is one and only if times repeat consistently
						{
							echo $events[$event_id]['display_date'] . '<br />';
							format_times($start[$i],$end[$i]);
							echo '<br />';
						}
					else
						{
							foreach($found_instances as $unique_instance)
							// Build a display date if there isn't one, or list each instance separately
							// if they happen on different times.
								{
									if(($i <= 4))
										{
											if($date[$i] != $date[$i - 1])
												{
													echo date("l, F, j, Y",$events[$event_id][$unique_instance]['start']) . '<br />';
												};
											format_times($start[$i],$end[$i]);
											if(($date[$i] != $date[$i+1]) or (($i == 4)))
												{ echo '<br />'; };
											echo '<br />';
											$i++;
										}
									else // truncate if you don't need to see all instances
										{
											echo '<a href="/index.php?section_id=9&ev_id='
											. $event_id . '&upcoming=all">'
											. ($instancecount - 5)
											. ' more &raquo;</a><br /><br />'; break;
										};
								};
						};
				}
			else // Only one instance found
				{
					if($events[$event_id]['display_date']) // Check for display date
						{
							echo $events[$event_id]['display_date'] . '<br />';
							format_times($start[$i],$end[$i]);
							echo '<br />';
						}
					else // If no display date
						{
							echo date("l, F, j, Y",$events[$event_id][$in_id]['start']) . '<br />';
							format_times($start[$i],$end[$i]);
							echo '<br /><br />';
						};
				};
		};

function get_events($GR_ID)
	{
    	global $GR_ID;
		$event_sql = "SELECT *, CONVERT(StartDateTime, char) AS start, CONVERT(EndDateTime, char) AS end_dt FROM mysql_web_db.events INNER JOIN mysql_web_db.instance ON instance.EV_ID = events.EV_ID INNER JOIN mysql_web_db.gr_ev G ON g.EV_ID = events.EV_ID WHERE g.GR_ID = '435' AND Instance.StartDateTime > CURRENT_TIMESTAMP() ORDER BY Instance.StartDateTime ASC, Instance.IN_ID";
		$event_results = mysql_query($event_sql);
		while($row = mysql_fetch_array($event_results, MYSQL_ASSOC))
		// Get event data from the calendar database
			{
				global $events;
				$event_id = $row['EV_ID'];
				$ev_id = $row['EV_ID'];
				$in_id = $row['IN_ID'];
				$events[$ev_id]['ev_title'] = $row['EV_Title'];
				$events[$ev_id]['ev_type'] = $row['EV_Type'];
				$events[$ev_id]['display_date'] = $row['EV_Display_Date'];
				$events[$ev_id]['location'] = $row['EV_Location'];
				$events[$ev_id]['description'] = preg_replace('[\r]', '<br />', $row['EV_Description']);
				$events[$ev_id][$in_id]['start'] = strtotime($row['StartDateTime']);
				$events[$ev_id][$in_id]['end_dt'] = strtotime($row['EndDateTime']);
			}

		foreach($events as $event_id => $keys)
		// Build an array we can manipulate and refer back to, rather than querying the database forever
			{
				foreach($keys as $in_id => $extra_data)
					{
						if(!array_key_exists('instances',$events[$event_id]))
							{
								if(is_numeric($in_id))
									{
										$instancecount++;
										$events[$event_id]['instances'] = $in_id;
									};
							}
						else
							{
								if(is_numeric($in_id))
									{
										$instancecount++;
										$events[$event_id]['instances'] = $events[$event_id]['instances']
										. ',' . $in_id;
									};
							};
					};
				$events[$event_id]['instancecount'] = $instancecount;
				unset($instancecount);
			};
			
		foreach($events as $event_id => $instance)
		// Build the page using the data pulled and formatted above
			{
				$listed_events[] = 'init';
				if(!in_array($event_id,$listed_events))
					{
						$listed_events[] = $event_id;
						echo "<div class='fourcol ev_info'>";
						echo "<div class='event callout'>";
						echo "<h3>".$events[$event_id]['ev_type']."</h3>";
						echo "<p class='ev_date'>";
						$found_instances = explode(',',$events[$event_id]['instances']);
						get_times($event_id,$found_instances,$events[$event_id]['instancecount']);
						//echo '
						//<br />';
					};
				// If you don't want descriptions and such, use the following code:
				/*
				echo '<p><a href="/index.php?section_id=9&ev_id=' . $event_id . '">more &raquo;</a></p>';
				*/
				// Comment out the following lines if you don't want the event description:
				///*
				echo "</p>";
				if($events[$event_id]['location'])
				{
					echo "<p class='location'>".$events[$event_id]['location']."</p>";
				}
				echo "</div></div><div class='eightcol last ev_desc'>";
				echo "<h2>".$events[$event_id]['ev_title']."</h2>";
				if($events[$event_id]['description'])
				{
					echo $events[$event_id]['description'] . "<br />"; 
				}
				echo "</div><hr />"; //end event div
				$has_content = 1;
			};
		if(!$has_content)
			{
				echo "<p>There are no more events scheduled related to <i>" . $exhibition_title . "</i>.</p>";
			};
			
	}; 

?>