<!DOCTYPE html>
<html>
<head>
	<title>XML File Generator</title>
</head>
<body>
	<h2>XML File Generator</h2>

	<?php

	$hostname 	= "localhost";
	$db_user	= "xxx";
	$db_pword 	= "xxx";
	$db_name	= "xxx";

    $mysqli = new mysqli( $hostname, $db_user, $db_pword, $db_name);

	/* check connection */
	if ($mysqli->connect_errno) {
	    printf("Connect failed: %s\n", $mysqli->connect_error);
	    exit();
	}

	$query = "SELECT * FROM articles";
	$result = $mysqli->query($query);

	 if (mysqli_num_rows($result) > 0) {

	 	$articleArray = array();

	 	$row_total = 0;

	 	while ($row = $result->fetch_assoc()) {

	 		$row_total++;

	 		// Check for valid entries
	 		if($row["live"] === '1' && $row["archived"] === '0') {

	 			array_push($articleArray, $row);
	 		}
        }

        echo $row_total . " rows read from the db <br />";

        if(count($articleArray)){

       		createXMLFile($articleArray);
        }

     } else {

        echo "0 results";
     }

	/* All done, close connection */
	$mysqli->close();


	function createXMLFile($array){

		echo "Creating " . sizeof($array) . " XML elements <br />";

		$filePath = 'articles.xml';
  		$dom = new DOMDocument('1.0', 'utf-8'); 
  		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
   		$root = $dom->createElement('urlset'); 

   		$root_attr = $dom->createAttribute("xmlns");
		$root->appendChild($root_attr);
		$root_attr_text = $dom->createTextNode("http://www.sitemaps.org/schemas/sitemap/0.9");
		$root_attr->appendChild($root_attr_text);		

	 	foreach($array as $row){

	 		$url = $dom->createElement('url');
	 			$loc = $dom->createElement('loc',  createSlugString($row["title"], "news"));
	 			$url->appendChild($loc); 									// ToDo: use 'time_modified' ??
	 			$lastmod = $dom->createElement('lastmod', date("Y-m-dTG:i", $row["time_created"]) . "+00:00");  
	 			$url->appendChild($lastmod);
	 			$image = $dom->createElement('image:image');  
	 				$image_loc = $dom->createElement('image:loc', createSlugString($row["title"], "images") . ".jpg");
					$image->appendChild($image_loc);
					$image_title = $dom->createElement('image:title', $row["title"]);
					$image->appendChild($image_title);
	 			$url->appendChild($image);
	 		$root->appendChild($url);
	 	}
	 	$dom->appendChild($root); 
		$dom->save($filePath); 
	}


	function createSlugString($title, $path){

		$slug_root = "http://www.xxxxx.xxx/" . $path . "/";

    	$mod1 = strtolower($title);									// Make it lowrcase
    	$mod2 = trim($mod1);										// Trim leading and trailing blank spaces
    	$mod3 = preg_replace('~[^0-9a-z\\s]~i', '', $mod2); 		// Removes specual chars
    	$slug_str1 = preg_replace('/[[:space:]]+/', '-', $mod3);  	// Replaces spaces with a dash
    	$slug_str2 = "'" . $slug_str1 . "'";

		return $slug_root . $slug_str1;
	}

	?>

</body>
</html>

