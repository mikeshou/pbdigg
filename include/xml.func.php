<?php

function xml_to_array($xml)
{
	$values = $index = $array = array();
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	if (0 === xml_parse_into_struct($parser, $xml, $values, $index))
	{
		return false;
	}
	xml_parser_free($parser);
	$i = 0;
	$name = $values[$i]['tag'];
	$array[$name] = isset ($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
	$array[$name] = struct_to_array($values, $i);

	return $array[$name];
}

function struct_to_array($values, &$i)
{
	$child = array ();
	if (isset ($values[$i]['value']))
	{
		array_push($child, $values[$i]['value']);
	}

	while ($i++ < count($values))
	{
		switch ($values[$i]['type'])
		{
			case 'cdata' :
				array_push($child, $values[$i]['value']);
				break;

			case 'complete' :
				$name = $values[$i]['tag'];
				if (!empty ($name))
				{
					$child[$name] = isset ($values[$i]['value']) ? ($values[$i]['value']) : '';
					if (isset ($values[$i]['attributes']))
					{
						$child[$name] = $values[$i]['attributes'];
					}
				}
				break;

			case 'open' :
				$name = $values[$i]['tag'];
				$size = isset ($child[$name]) ? sizeof($child[$name]) : 0;
				$child[$name][$size] = struct_to_array($values, $i);
				break;

			case 'close' :
				return $child;
				break;
		}
	}
	return $child;
}
?>