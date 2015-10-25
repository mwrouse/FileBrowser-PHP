<?php
	# Set Time Zone to Central Time
	date_default_timezone_set("America/Chicago");
	
	# Base URL
	$url = $_SERVER["SERVER_NAME"];
	
	# Array of items NOT to list
	$doNotList = array(".htaccess", "index.html", "index.htm", "index.php", "cgi-bin", "error_log", "cprou");
	
	# Get the current directory
	$thisDir = substr($_SERVER['REQUEST_URI'], 1);
	
	# Redirect if folder/file doesn't exist
	if (!is_dir($thisDir) && $thisDir != $rootDir){
		echo "Directory/File Does Not Exist";
		echo "<script>window.location.href=\"http://$url\";</script>";
	}
	
	# Open current directory 
	if ($thisDir == ""){
		$parentDir = opendir("../");
		$myDirectory = opendir(".");
	}else{
		$myDirectory = opendir($thisDir);
	}

	# Directories and files are in different arrays so directories can be listed before files
	$dirArray = array(); 	# Directory names
	$filesArray = array(); 	# File names 
	
	while ($indexName = readdir($myDirectory)){
		# Only add visible files to the directories
		if (substr("$indexName", 0, 1) != "." && !in_array($indexName, $doNotList)){
			if (filetype($thisDir.$indexName) == "dir"){
				# Add to directories array
				$dirArray[] = $thisDir.$indexName;
			}else{
				# Add to files array
				$filesArray[] = $thisDir.$indexName;
			}
		}
	}
	
	# Close current directory
	closedir($myDirectory);

	# Count each item in each directory
	$dirCount = count($dirArray);
	$fileCount = count($filesArray);
	
	# Sort the arrays alphabetically
	if ($dirCount != 0){natcasesort($dirArray);}
	if ($fileCount != 0){natcasesort($filesArray);}
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?=$thisDir?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<style>
			body{font:14px;font-family:"Segoe UI"}a{text-decoration:none}a:hover{text-decoration:underline}table{width:100%;border-collapse:collapse}tr:nth-of-type(odd){background:#eee}th{background:#333;color:#fff;font-weight:700}td,th{padding:6px;border:1px solid #ccc;text-align:left}
			td{word-wrap:break-word;}h1{width:100%;word-wrap:break-word;}th[name]{width:50%;}th[type]{width:15%;}th[size]{width:10%;}th[modified]{width:25%;}
		</style>
		<!--[if !IE]><!-->
		<style>
			@media only screen and (max-width: 760px),(min-device-width: 768px) and (max-device-width: 1024px){table,thead,tbody,th,td,tr{display:block}thead tr{position:absolute;top:-9999px;left:-9999px}tr{border:1px solid #ccc}td{border:none;border-bottom:1px solid #eee;position:relative;padding-left:50%}td:before{position:absolute;top:6px;left:6px;width:45%;padding-right:10px;white-space:nowrap}td:nth-of-type(1):before{content:"Name:"}td:nth-of-type(2):before{content:"Type:"}td:nth-of-type(3):before{content:"Size:"}td:nth-of-type(4):before{content:"Modified:"}}@media only screen
			and (min-device-width : 320px)
			and (max-device-width : 480px){body{padding:0;margin:0;width:320px}}@media only screen and (min-device-width: 768px) and (max-device-width: 1024px){body{width:495px}}
		</style>
		<!--<![endif]-->
	</head>
	<body>
		<h1>Index of <?=$url?>/<?=$thisDir?></h1>
		<?=$fileCount?> <?if($fileCount != 1){?>files<?}else{?>file<?}?> | <?=$dirCount?> <?if($dirCount != 1){?>directories<?}else{?>directory<?}?><br/>
		<table>
			<thead>
				<tr>
					<th name>Name</th>
					<th type>Type</th>
					<th size>Size</th>
					<th modified>Modified</th>
				</tr>
			</thead>
			<tbody>
				<?
				# List parent directy as the first index
				if ($thisDir != ""){
					print("<tr><td><a href=\"../\">../</a></td><td>Parent Directory</td><td>--</td><td>--</td></tr>");
				}
				
				# List all the directories first
				if ($dirCount != 0){
					foreach ($dirArray as $index){
						print "<tr><td><a href=\"".str_replace($thisDir, '', $index)."\">/".ucwords(str_replace('+', ' ', str_replace($thisDir, '', $index)))."/</a></td><td>Directory</td><td>". convert(dirSize($index))."</td><td>".dateModified($index)."</td></tr>";
					}
				}
				
				# List all the files
				if ($fileCount != 0){
					foreach ($filesArray as $index){
						print "<tr><td><a href=\"".str_replace($thisDir, '', $index)."\">".ucwords(str_replace('+', ' ', str_replace($thisDir, '', $index)))."</a></td><td>".getFileType($index)."</td><td>".convert(filesize($index))."</td><td>".dateModified($index)."</td></tr>";
					}
				}
				?>
				
			</tbody>
		</table>

	</body>
</html>
                            
<?php
# ================================
#        Name: convert
#      Author: Michael Rouse
#  Parameters: $OrigSize - Size of the file to convert
# Description: Will use the file size to generate a friendly format, e.g., "12 MBs"
#      Return: The file size in friendly form as a string
# ================================
function convert($OrigSize){
	# Size in Bytes (Default)
	$size = $OrigSize;
	$label = "B";
	
	if ($size >= 1024){
		# Get size in KBs
		$size /= 1024;
		$label = "KB";
		
		if ($size >= 1024){
			# Get size in MBs
			$size /= 1024;
			$label = "MB";
			
			if ($size >= 1024){
				# Get size in GBs
				$size /= 1024;
				$label = "GB";
			
				if ($size >= 1024){
					# Get size in TBs
					$size /= 1024;
					$label = "TB";
					
					if ($size >= 1024){
						# Get size in PBs
						$size /= 1024;
						$label = "PB";
					}
				}
			}
		}
	}
	
	# Return the size to two decimal places and the label
	return round($size, 2)." ".$label;
}


# ================================
#        Name: Date Modified
#      Author: Michael Rouse
#  Parameters: $fileName - The full path to a file
# Description: Will generate a friendly version of the last modified date
#      Return: The modified in friendly form as a string
# ================================
function dateModified($fileName){
	// Last modified time
	$mTime = filemtime($fileName);
	
	// Get each part of the date last modified
	$month = date("M", $mTime);
	$day = date("d", $mTime);
	$year = date("Y", $mTime);
	$hour = date("g", $mTime);
	$minutes = date("i", $mTime);
	
	// String of the final formatted date
	$dateString = ""; 
	
	// Check in the same month
	if ($month == date("M")){
		// Check if on the same day
		if ($day == date("d")){
			$dateString .= "Today ";
			
		// Check if yesterday
		}elseif(intval($day) == (intval(date("d")) - 1)){
			$dateString .= "Yesterday ";
 		}
	}else{
		// Check if yesterday was another month
		$lastMonth = intval(date("m")) - 1;
		if ($lastMonth < 0){$lastMonth = 12;}
		
		if (intval(date("m", $mTime)) == $lastMonth){
			if (date("d") == "01" && intval(date("m", $mTime)) == cal_days_in_month(CAL_GREGORIAN, date("m", $mTime), $year)){
				$dateString .= "Yesterday ";
			}
		}
	}
	
	if ($dateString == ""){
		$dateString = $month . ". " . $day . ", " . $year . " " . $hour . ":" . $minutes . " " . date("a", $mTime)." CST";
	}else{
		$dateString .= $hour . ":" . $minutes . " " . date("a", $mTime)." CST";
	}
	
	return $dateString;
	
}

# ================================
#        Name: Get File Type
#      Author: Michael Rouse
#  Parameters: $fileName - the location and name of the file
# Description: Will generate a friendly name for the file type, e.g., "Microsoft Word Document"
#      Return: The file type in friendly form as a string
# ================================
function getFileType($fileName){
	$extension = strtolower(substr(strrchr($fileName, '.'), 1)); // Get the extension from the file name
	
	$images = ["jpg", "jpeg", "exif", "bmp", "png", "gif", "tiff", "svg"];
	$text = ["doc", "docx", "log", "txt", "html", "htm", "php", "asp", "xml", "exls", "exlsx", "pptx", "py", "pyw"];
	$audio = ["mp3", "wav", "wma"];
	$videos = ["avi", "flv", "mp4", "mov", "wmv", "swf"];

	$fileTypes = ["jpg"  => "Image", 
			"jpeg" => "Image", 
			"exif" => "Image", 
			"bmp" => "Image", 
			"png" => "Image", 
			"gif" => "Image",
			"tiff" => "Image",
			"svg" => "Image",
			"doc" => "Microsoft Word Document", 
			"docx" => "Microsoft Word Document", 
			"pptx" => "PowerPoint Document",
			"exls" => "Excel Spreadsheet",
			"exlsx" => "Excel Spreadsheet",
			"log" => "Log", 
			"txt" => "Text Document", 
			"html" => "HTML Document", 
			"htm" => "HTML Document", 
			"php" => "PHP Document",
			"asp" => "ASP.Net Document",
			"xml" => "XML Document",
			"py" => "Python Program",
			"pyw" => "Python Program",
			"mp3" => "MP3 Audio", 
			"wav" => "WAVE Audio", 
			"wma" => "Windows Media Audio",
			"avi" => "Audio Video Interleave", 
			"flv" => "Flash Video", 
			"mp4" => "MPEG-4 Video", 
			"mov" => "Apple Quicktime Video",
			"wmv" => "windows Media Video", 
			"swf" => "Shockwave Flash Video"
			];
			
	// Return file type if it's known, otherwise just return the extension
	if (in_array($extension, $images) or in_array($extension, $text) or in_array($extension, $audio) or in_array($extension, $videos)){
		// Known file type
		return ($fileTypes[$extension]. " (." . $extension .")");
	}else{			
  		return $extension;
  	}
}

function dirSize($directory) {
    $size = 0;
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
        $size+=$file->getSize();
    }
    return $size;
} 
  ?>                  
