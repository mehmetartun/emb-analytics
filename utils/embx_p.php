<?php

function embx_sql($sql) {
	$ret = [];
	$rs = mysql_query($sql);
		if ($rs ){
			if (mysql_num_rows($rs) > 0) {
				$row = mysql_fetch_assoc($rs);
				$j = 0;
				do {
					foreach($row as $key => $value) {
						$ret[$j][$key] = $value;	
					};
					$j=$j+1;
				} while ($row = mysql_fetch_assoc($rs));
			};
		};
	return $ret;
}
?>