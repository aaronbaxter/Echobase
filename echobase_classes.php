<?php
ini_set("memory_limit","1024M");
class terrainParser {
	var $logfile = "log.txt";

	function __construct(){
		
	}

	function createAscTile($tileinfo){
		$data = array();

		//srtm terrain data only extends to +60 or -60 latitude
		// check to see if tile meets these criteria else create default tile with z=1
		if($tileinfo["bounds"]["north"]<60 OR $tileinfo["bounds"]["south"]>-60){
			$source = "raw/srtm/" . $tileinfo["srtm_code"] . ".zip";
			$bounds = $tileinfo["bounds"];
			$ascUnzipped = "tmp/" . $tileinfo["srtm_code"] . ".asc";
			if (!file_exists($ascUnzipped)) {
			    $this->unzipIt($source);
			}

			$tileData = ""; //tile data in string format

			$noRowsToGet;
			$noColumnsToGet;

			$yLine; //the first row to get
			$yTop; //the lat coords of the first data row--Line 7

			$xLine; //the first column to get

			//file info array
			$fileinfo = array();

			//for debugging
			$debug = "";

			// Load File Object 
			$file = new SplFileObject($ascUnzipped);

			$firstLine = 7; //the line that the actual data starts on
			$lastLine = 6006; // for the 5x5 tiles.

			//construct fileinfo
			for($i=0;$i<6;$i++){
				$file->seek($i);
				$linearray = explode(" ",$file->current());
				$fileinfo[$linearray[0]] = trim(end($linearray));
			}

			//lat value of the first data row
			$yTop = $fileinfo["yllcorner"] + $fileinfo["nrows"]*$fileinfo["cellsize"] - $fileinfo["cellsize"];

			// determine the initial row
			$yRowInitial = ($yTop - $bounds["north"]) / $fileinfo["cellsize"];
			//skip to the first line of data
			$yRowInitial = $yRowInitial + $firstLine - 1; // -1 cause SplFileInfo is 0 based
			$yRowInitial = round($yRowInitial);

			//determine the number of rows to get
			$noRowsToGet = round((($bounds["north"] - $bounds["south"]) / $fileinfo["cellsize"]) + 1);
			$debug .= "noRowsToGet: " . $noRowsToGet . "<br>";

			// set the final row
			$yRowFinal = $yRowInitial + $noRowsToGet;
			// $yRowFinal = $yRowInitial + 25;

			// set the initial column
			$yColumnInitial = ($bounds["west"] - $fileinfo["xllcorner"] ) / $fileinfo["cellsize"];

			//determine number of columns to get
			$noColumnsToGet = round((($bounds["east"] - $bounds["west"]) / $fileinfo["cellsize"]) + 1 );
			$debug .= "noColumnsToGet: " . $noColumnsToGet . "<br>";		

			// set the final column
			$yColumnFinal = $yColumnInitial + $noColumnsToGet;

			$rows = array();

			for($yRowInitial; $yRowInitial < $yRowFinal; $yRowInitial++){
				$file->seek($yRowInitial);
				//HACK: If selecting tiles on the northern bound, just copy the next lat data twice to avoid having to download and parse the next tile
				if($yRowInitial == 5) {$file->seek($yRowInitial + 1);}
				// $rows[] = $file->current();
				$latitude = $yTop - ($yRowInitial-6)*$fileinfo["cellsize"];
				$debug .= "yRowInitial: ". $yRowInitial ."; lat:".$latitude."<br>";
				// echo $latitude  . "<hr/>";
				$explodedRow = explode(" ", $file->current());
				// var_dump($explodedRow);
				// loop through the columns
				for($i = 0; $i < $noColumnsToGet; $i++){
					$arrayVal = $yColumnInitial + $i;
					//HACK: If selecting tiles on the eastern bound, just copy the last long value twice to avoid having to get the next tile
					if($arrayVal > 5999){$arrayVal = 5999;}
					$z = $explodedRow[$arrayVal];
					
					$longitude = $fileinfo["xllcorner"] + ($yColumnInitial + $i) * $fileinfo["cellsize"];
					//round to 5 decimal places for the echoscapes app
					$data[] = round($longitude,5) . " " . round($latitude,5) . " " . $z;
					$debug .= "arrayVal: " . $arrayVal . "; lon: ".$longitude."; Z: " .$z . "<br>";
				}
				
			}
			// echo $debug;
			return $data;
		} else {
			// data is further north or south than the supplied srtm data
			$lon_init = $tileinfo["bounds"]["west"];
			$lat_init = $tileinfo["bounds"]["south"];
			$increment = 0.00083333333333333;
			for($x=0;$x<25;$x++){
				//loop through x
				$lon = $lon_init + $x*$increment;
				for($y=0;$y<25;$y++){
					//loop through y
					$lat = $lat_init + $y*$increment;
					$data[] = round($lon,5) . " " . round($lat,5) . " 1";
				}
			}
			return $data;
		}
	}

	function writeDummyAscTile($tileinfo){
		$data = array();
		$lon_init = $tileinfo["bounds"]["west"];
		$lat_init = $tileinfo["bounds"]["south"];
		$increment = 0.00083333333333333;
		for($x=0;$x<25;$x++){
			//loop through x
			$lon = $lon_init + $x*$increment;
			for($y=0;$y<25;$y++){
				//loop through y
				$lat = $lat_init + $y*$increment;
				$data[] = round($lon,5) . " " . round($lat,5) . " 1";
			}
		}
		$writeDir = 'data/asc90m/' . substr($tileinfo["tilename"],0,-4);
		//check if writeDir exists and if not create it
		if (!file_exists($writeDir)) {
		    mkdir($writeDir, 0777, true);
		}
		file_put_contents($writeDir . "/" . $tileinfo["tilename"].".asc", implode("\n",$data));
		$logtext = "wrote tile ". $tileinfo["tilename"].".asc at " . time() . "\n";
		file_put_contents("log.txt", $logtext, FILE_APPEND | LOCK_EX);
	}

	function unzipIt($zipSource){
		$zip = new ZipArchive;
		var_dump($zip);
		$res = $zip->open($zipSource);
		if ($res === TRUE) {
		  $zip->extractTo('tmp/');
		  $zip->close();
		  echo 'woot!';
		} else {
		  echo 'doh!';
		}

	}

	function writeAscTile($tileinfo){
		$terrainData = $this->createAscTile($tileinfo);
		$writeDir = 'data/asc90m/' . substr($tileinfo["tilename"],0,-4);
		//check if writeDir exists and if not create it
		if (!file_exists($writeDir)) {
		    mkdir($writeDir, 0777, true);
		}
		file_put_contents($writeDir . "/" . $tileinfo["tilename"].".asc", implode("\n",$terrainData));
		$logtext = "wrote tile ". $tileinfo["tilename"].".asc at " . time() . "\n";
		file_put_contents("log.txt", $logtext, FILE_APPEND | LOCK_EX);

	}

}

class osmParser {
	// $GLOBALS

	function __construct(){
		
	}



	function createOsmTile($tileinfo){
		$filename = $tileinfo["tilename"];
		$bounds = $tileinfo["bounds"];

		//specify write directory
		$writeDir = 'data/o5m/' . substr($filename,0,-4);
		//check if writeDir exists and if not create it
		if (!file_exists($writeDir)) {
		    mkdir($writeDir, 0777, true);
		}
		$filepath =  $writeDir . "/" . $filename . ".o5m";
		
		//extract 0.02 by 0.02 tile from source data
		$cmd = 'osmconvert --drop-version -b="' . $bounds["west"] .','. $bounds["south"] .','. $bounds["east"] .','. $bounds["north"] . '" raw/osm/' . $tileinfo["osm_code"]["osm_code"] . '.osm.pbf --out-o5m -o="'.$filepath.'"';
		// echo $extract_cmd;
		exec($cmd);

		/*
			Rewrote OSM base tile approach: deprecated

			//filter 0.02 by 0.02 tile to remove unecessary data
			
			//NOTE: Echoscapes is very particular and only accepts data filtered in this fashion
			$filter_cmd = 'osmfilter --parameter-file="prm/osmfilter_params" ' . $tmpPath . ' -o="'. $filepath . '"';
			// echo "<br>";
			// echo $filter_cmd;
			exec($filter_cmd);
		
		 */
		// $logtext = "wrote tile ". $tileinfo["tilename"].".o5m at " . time() . "\n";
		// file_put_contents("log.txt", $logtext, FILE_APPEND | LOCK_EX);

	}
}

class echobase {
	var $logfile = "log.txt";

	function __construct(){
	}

	function login(){
		//authenticate via audiomob, require admin access
		//authentication required as uploads to server will be needed
	}

	function checkCoverage(){
		//get list of lat long from audio-mobile.org
		$nodes_json = file_get_contents("http://audio-mobile.org/rest/heatmap.json");
		$nodes = json_decode($nodes_json);

		// initiate the tile array
		$tilesNeeded = array();
		$srtmNeeded = array();

		// iterate through the node list to find needed tiles
		foreach($nodes as $node){
			$tileinfo = $this->xy2tile($node->lon,$node->lat);
			$tilesNeeded[] = $tileinfo["tilename"];
			$srtmNeeded[] = $tileinfo["srtm_code"];
		}

		// filter for unique values
		$uniqueTilesNeeded = array_unique($tilesNeeded);
		$uniqueSrtmNeeded = array_unique($srtmNeeded);

		// get existing tile list
		$existingTiles = $this->getExistingTileList();

		//compare new versus existing
		$missingTiles = array();
		$missingTiles["o5m"] = array_diff($uniqueTilesNeeded, $existingTiles["o5mTiles"]);
		$missingTiles["asc"] = array_diff($uniqueTilesNeeded, $existingTiles["ascTiles"]);
		
		// $logtext = count($missingTiles["o5m"]). " o5m tiles to write and ". count($missingTiles["asc"])." tiles to write at " . time() . "\n";
		// file_put_contents("log.txt", $logtext, FILE_APPEND | LOCK_EX);

		//return missing tiles
		return $missingTiles;



	}

	function missingTiles2BaseTileCodes($missingTiles){
		$srtmNeeded = array();
		$osmNeeded = array();
		foreach ($missingTiles["o5m"] as $key => $value) {
			$bounds = $this->tilename2bounds($value);
			$tileinfo = $this->xy2tile($bounds["west"]+0.01,$bounds["south"]+0.01);
			$osmNeeded[] = $tileinfo["osm_code"]["osm_code"];
		}
		foreach ($missingTiles["asc"] as $key => $value) {
			$bounds = $this->tilename2bounds($value);
			$tileinfo = $this->xy2tile($bounds["west"]+0.01,$bounds["south"]+0.01);
			$srtmNeeded[] = $tileinfo["srtm_code"];
		}
		$srtmNeeded = array_unique($srtmNeeded);
		$osmNeeded = array_unique(($osmNeeded));
		// print_r($srtmNeeded);
		$return["osm"] = $osmNeeded;
		$return["srtm"] = $srtmNeeded;
		return $return;
	}


	function getExistingTileList(){
		//check what o5m and asc tiles we already have generated
		$o5mTiles = array();
		$ascTiles = array();
		
		$pathToTileFiles = $_SERVER["DOCUMENT_ROOT"]."/echoscapes/data";

		//get o5m list
		$it = new RecursiveDirectoryIterator($pathToTileFiles);
		$it->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
		$display = Array ( 'o5m');
		foreach(new RecursiveIteratorIterator($it) as $file)
		{
		    if (in_array(strtolower(array_pop(explode('.', $file))), $display)){
		        $filename = $file->getFileName();
		        $filename = substr($filename,0,-4);
		        $o5mTiles[] = $filename;
		    }   
		}
		
		//get asc list
		$it = new RecursiveDirectoryIterator($pathToTileFiles);
		$it->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
		$display = Array ( 'asc');
		foreach(new RecursiveIteratorIterator($it) as $file)
		{
		    if (in_array(strtolower(array_pop(explode('.', $file))), $display)){
		        $filename = $file->getFileName();
		        $filename = substr($filename,0,-4);
		        $ascTiles[] = $filename;
		    }   
		}

		//return tile list
		$return["o5mTiles"] = $o5mTiles;
		$return["ascTiles"] = $ascTiles;
		return $return;
	}

	function xy2tile($x,$y){
		// $NOTES
		// tile begins with a letter denoting the quadrant followed by the tile numbers for the x then the y
		// tile numbers count from the lower left hand corner of the 1x1 degree tile
		// $VARIABLES
		$longitudinalLetter;
		$latitudinalLetter;
		$xTileBase;
		$yTileBase;
		$west;
		$east;
		$south;
		$north;
		$xTileNumber;
		$yTileNumber;
		$tilename;
		$return = array();

		// construct longitudinalLetter
		if($x<0){
			$longitudinalLetter = "w";
			$west = floor($x);
			$xTileBase = abs($west);
		}
		else{
			$longitudinalLetter = "e";
			$xTileBase = floor($x);
			$west = $xTileBase;
		}

		// construct latitudinalLetter 
		if ($y<0){
			$latitudinalLetter = "s";
			$south = floor($y);
			$yTileBase = abs($south);
		}
		else{
			$latitudinalLetter ="n";
			$yTileBase = floor($y);
			$south = $yTileBase;
		}

		//construct the x tile number
		$xTileNumber = abs($x)-floor(abs($x));
		// echo $xTileNumber . "<hr/>";
		$xTileNumber = floor($xTileNumber*100/2);
		if($x<0){$xTileNumber = 49-$xTileNumber;}

		// construct the y tile number
		$yTileNumber = abs($y)-floor(abs($y));
		$yTileNumber = floor($yTileNumber*100/2);
		if($y<0){$yTileNumber = 49-$yTileNumber;}

		//construct bounds
		$west = $west + $xTileNumber*0.02;
		$east = $west + 0.02;
		$south = $south + $yTileNumber*0.02;
		$north = $south + 0.02;
		
		//all xy tiles must be two digits
		if($xTileNumber<10){$xTileNumber = "0" . $xTileNumber;}
		if($yTileNumber<10){$yTileNumber = "0" . $yTileNumber;}

		//construct tilename
		$tilename = $latitudinalLetter . $yTileBase . $longitudinalLetter . $xTileBase  . $xTileNumber . $yTileNumber;

		//construct SRTM 5x5 tilename
		$srtmTileName = $this->xy2srtmTileCode($x,$y);
		$osminfo = $this->xy2osmTileCode($x,$y);

		// build return array
		$return["tilename"] = $tilename;
		$return["srtm_code"] = $srtmTileName;
		//give the 5 degree tilename
		$return["osm_code"] = $osminfo["5"];
		$return["bounds"]["west"] = $west;
		$return["bounds"]["east"] = $east;
		$return["bounds"]["south"] = $south;
		$return["bounds"]["north"] = $north;
		return $return;

	}

	function xy2srtmTileCode($x,$y){
		$srtm_x;
		$srtm_y;

		$srtm_x = ceil((180+$x)/5);
		$srtm_y = ceil((60-$y)/5);

		if($srtm_x<10){$srtm_x = "0" . $srtm_x;}
		if($srtm_y<10){$srtm_y = "0" . $srtm_y;}

		return "srtm_" . $srtm_x . "_" . $srtm_y;
	}

	function xy2osmTileCode($x,$y){
		$osm_x;
		$osm_y;
		$return = array();

		//5by5 code
		$osm_x = ceil((180+$x)/5);
		$osm_y = ceil((90-$y)/5);

		$s = 90 - ($osm_y*5);
		$n = $s + 5;
		$e = ($osm_x * 5) - 180;
		$w = $e - 5;

		if($osm_x<10){$osm_x = "0" . $osm_x;}
		if($osm_y<10){$osm_y = "0" . $osm_y;}

		$osmTileCode = "osm_" . $osm_x . "_" . $osm_y;

		$bounds["south"] = 90 - ($osm_y*5);
		$bounds["north"] = $s + 5;
		$bounds["east"] = ($osm_x * 5) - 180;
		$bounds["west"] = $e - 5;

		$return["5"]["osm_code"] = $osmTileCode;
		$return["5"]["bounds"] = $bounds;
		
		//15by15code
		
		$osm_x = ceil((180+$x)/15);
		$osm_y = ceil((90-$y)/15);

		$s = 90 - ($osm_y*15);
		$n = $s + 15;
		$e = ($osm_x * 15) - 180;
		$w = $e - 15;

		if($osm_x<10){$osm_x = "0" . $osm_x;}
		if($osm_y<10){$osm_y = "0" . $osm_y;}

		$osmTileCode = "osm_" . $osm_x . "_" . $osm_y;

		$bounds["south"] = 90 - ($osm_y*15);
		$bounds["north"] = $s + 15;
		$bounds["east"] = ($osm_x * 15) - 180;
		$bounds["west"] = $e - 15;

		$return["15"]["osm_code"] = $osmTileCode;
		$return["15"]["bounds"] = $bounds;
		
		//90by90 code

		$osm_x = ceil((180+$x)/90);
		$osm_y = ceil((90-$y)/90);

		$s = 90 - ($osm_y*90);
		$n = $s + 90;
		$e = ($osm_x * 90) - 180;
		$w = $e - 90;

		if($osm_x<10){$osm_x = "0" . $osm_x;}
		if($osm_y<10){$osm_y = "0" . $osm_y;}

		$osmTileCode = "osm_" . $osm_x . "_" . $osm_y;

		$bounds["south"] = 90 - ($osm_y*90);
		$bounds["north"] = $s + 90;
		$bounds["east"] = ($osm_x * 90) - 180;
		$bounds["west"] = $e - 90;

		$return["90"]["osm_code"] = $osmTileCode;
		$return["90"]["bounds"] = $bounds;
		
		return $return;
	}

	function tilename2bounds($tilename){
		$x_base;
		$x_polarity;
		$y_base;
		$y_polarity;
		$x_tileCode;
		$y_tileCode;

		$tmp_yStrLen;

		$bounds = array();

		if(strpos($tilename,".")!==false){
			$tilename = substr($tilename,0,-4);
		}
		$tileCode = substr($tilename,-4);

		//check longitudinal base tile
		if(strpos($tilename,"w")!==false){
			$y_base = strstr($tilename, 'w', true);
			$x_polarity = -1;
		} else {
			$y_base = strstr($tilename, 'e', true);
			$x_polarity = 1;
		}

		$tmp_yStrLen = strlen($y_base);

		//check latitudinal base tile 
		$x_base = substr($tilename, 0, -4);
		$x_base = substr($x_base,$tmp_yStrLen + 1);
		if(strpos($tilename,"s")!==false){
			$y_polarity = -1;
		} else {
			$y_polarity = 1;
		}
		$y_base = substr($y_base,1);

		// adjust polarity
		$y_base = $y_base * $y_polarity;
		$x_base = $x_base * $x_polarity;

		// format tile codes less than 10
		$x_tileCode = substr($tileCode,0,2);
		if(substr($x_tileCode,0,1)==0){
			$x_tileCode = substr($x_tileCode,1);
		}
		$y_tileCode = substr($tileCode,-2);
		if(substr($y_tileCode,0,1)==0){
			$y_tileCode = substr($y_tileCode,1);
		}

		// return bounds
		$bounds["west"] = $x_base + $x_tileCode*0.02;
		$bounds["south"] = $y_base + $y_tileCode*0.02;
		$bounds["east"] = $x_base + $x_tileCode*0.02 + 0.02;
		$bounds["north"] = $y_base + $y_tileCode*0.02 + 0.02;

		return $bounds;
	}

}

?>