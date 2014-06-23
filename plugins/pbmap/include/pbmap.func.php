<?php

!defined('IN_ADMIN') && exit('Access Denied!');

function mapdate($timestamp)
{
	global $pb_timezone;
	if ($pb_timezone == '111')
	{
		$offset = '+00:00';
	}
	else
	{
		$offpre = '+';
		if ($pb_timezone{0} == '-')
		{
			$pb_timezone = substr($pb_timezone, 1);
			$offpre = '-';
		}
		if (strpos($pb_timezone, '.') !== FALSE)
		{
			list($hour, $minute) = explode('.', $pb_timezone);
			$minute = ':'. (60 * ('0.'.$minute));
		}
		else
		{
			$hour = $pb_timezone;
			$minute = ':00';
		}
		if ($hour < 10) $hour = '0'.$hour;
		$offset = $offpre.$hour.$minute;
	}
	return gdate($timestamp, 'Y-m-d').'T'.gdate($timestamp, 'H:i:s').$offset;
}
?>