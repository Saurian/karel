<?php

function removeFiles($source){
	$folder = opendir($source);
	while($file = readdir($folder))	{
		if ($file == '.' || $file == '..') {
			continue;
		}
	       
		if(is_dir($source.'/'.$file)){
			removeFiles($source.'/'.$file);
		}
		else {
			@unlink($source.'/'.$file);
            echo 'mazu soubor: '.$source.'/'.$file.'<br />';
		}
		       
	}
	closedir($folder);
	rmdir($source);
	return 1;
}
removeFiles('../temp/cache');
//removeFiles('log');
?>