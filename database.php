<?php header('Content-Type: text/html; charset=utf-8');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "definitions";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Checks database for English word.
// Returns translated word if found, else check if there is a prefix in the way
function checkDatabase($eng, $lang2, $fill2, $conn2){
    $sql = "SELECT " . $lang2 . ", disambig, pos, noun, verb FROM DefinitionsTable WHERE english='" . $eng . "'";
    $result = $conn2->query($sql);
    $output2 = "";

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $output2 = sortOutCaps($eng, $row[$lang2]);  
    }
    elseif ($result->num_rows > 1) {
        $output2 = "<select name='disambig' onchange='changeToText(this);'>";
        $output2 .= "<option value='opt0'>" . $eng . "</option>";
        while($row = $result->fetch_assoc()) {
			$case = sortOutCaps($eng, $row[$lang2]);            
            $output2 .= "<option value='" . $case . "'>" . $case . " (" . $row["pos"];
            if($row["noun"]!="") {
                $output2 .= " " . $row["noun"];
            }
            if($row["verb"]!="") {
                $output2 .= " " . $row["verb"];
            }
            if($row["disambig"]!="") {
                $output2 .= ", " . $row["disambig"];
            }
            $output2 .= ")</option>";
        }
        $output2 .= "<option value='" . $eng . "'>none of these</option>";
        $output2 .= "</select>";
    }
    else {
        $output2 .= checkPrefix($eng, $lang2, $fill2, $conn2);
    }
    return $output2;
}

// Checks if there is a prefix that matches the database
// If there is one, then it takes the rest of the word and passes it back to checkDatabase
// If nothing is found, then it passes the word to hashUnknown
function checkPrefix($eng2, $lang3, $fill3, $conn3){
    $index = 1;
    $pref = [];
    for ($i = -1; $i > -strlen($eng2); $i--) {
        $sql = "SELECT " . $lang3 . ", disambig, pos FROM DefinitionsTable WHERE english='" . substr($eng2,0,$i) . "' AND pos='PREFIX'";
        $result2 = $conn3->query($sql);
        
        if ($result2->num_rows == 1) {
            $row = $result2->fetch_assoc();
            $pref = sortOutCaps($eng2, $row[$lang3]);
            $index = $i;
            break;
        }
        elseif ($result2->num_rows > 1) {
            $build = "<select name='disambig' onchange='changeToText(this);'>";
            $build .= "<option value='opt0'>" . $eng2 . "</option>";
            while($row = $result2->fetch_assoc()) {
				$case = sortOutCaps($eng2, $row[$lang3]);
                $build .= "<option value='" . $case . "'>" . $case . " (" . $row["pos"];
                if($row["noun"]!="") {
                    $build .= " " . $row["noun"];
                }
                if($row["verb"]!="") {
                    $build .= " " . $row["verb"];
                }
                if($row["disambig"]!="") {
                    $build .= ", " . $row["disambig"];
                }
                $build .= ")</option>";
            }
            $build = substr($build, 0, -1);
            $build .= "<option value='" . $eng2 . "'>none of these</option>";
            $build .= "</select>";
            $pref = $build;
            $index = $i;
            break;
        }
    }
    if($index != 1) {
        $output3 = $pref;
        $output3 .= checkDatabase(substr($eng2,$index),$lang3, $fill3, $conn3);
    }
    else {
        if($fill3=="yes") {
            $output3 = "<font color='red'>" .  hashUnknown($eng2, $lang3) . "</font>";
        }
        else {
           $output3 = "<font color='red'>" .  $eng2 . "</font>"; 
        }
    }
    return $output3;
}

// Makes sure the capitalization doesn't change
function sortOutCaps($eng, $word) {
	if(ctype_upper($eng[0])) {
		if(ctype_upper($eng)&&(strlen($eng)>1)) {
			$case = mb_strtoupper($word);
		}
		else {
			$case = mb_convert_case($word,MB_CASE_TITLE);
		}
		}
	else {
		$case = $word;
	}
	return $case;
}


// Hashes unknown word
function hashUnknown($eng3, $lang4) {
    // Pad short words
    $word = mb_strtolower($eng3);
    $len = strlen($word);
    for ($x = 0; $x < (8-$len); $x++) {
        $word = $word . "_";
    }
    $h = 196613;
    for ($y = 0; $y < strlen($word); $y++) {
        $h = ($h*12289) + ord($word[$y]);
    }
    $hashed = "";
    if ($lang4=="dwarf") {
        $hashed = hashDwarfLookup(fmod($h,4242182448));
    }
    if ($lang4=="elf") {
        $hashed = hashElfLookup(fmod($h,4254852000));
    }
    if ($lang4=="human") {
        $hashed = hashHumanLookup(fmod($h,4027785300));
    }
    if ($lang4=="goblin") {
        $hashed = hashGoblinLookup(fmod($h,4267517800));
    }
    if(ctype_upper($eng3[0])) {
        if(ctype_upper($eng3)&&(strlen($eng3)>1)) {
            $case = mb_strtoupper($hashed);
        }
        else {
            $case = mb_convert_case($hashed,MB_CASE_TITLE);
        }
    }
    else {
        $case = $hashed;
    }         

    return $case;
}

function hashDwarfLookup($num) {
    $c1lookup1 = [5, 6, 7, 8, 9, 10, 12, 14, 16, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28];
    $c1lookup2 = ["", "b", "c", "d", "f", "g", "k", "l", "m", "n", "r", "s", "t", "v", "z", "st", "ng", "sh", "th",];
    $vlookup1 = [14, 15, 16, 17, 18, 19, 31, 32, 33, 34, 35, 46, 47, 48, 49, 50, 62, 63, 64, 65, 66, 76, 77, 78, 79];
    $vlookup2 = ["a", "à", "á", "â", "ä", "å", "e", "è", "é", "ê", "ë", "i", "ì", "í", "î", "ï", "o", "ò", "ó", "ô", "ö", "u", "ù", "ú", "û"];
    $c2lookup1 = [1, 2, 3, 4, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17];
    $c2lookup2 = ["b", "d", "g", "k", "l", "m", "n", "r", "s", "t", "z", "st", "ng", "sh", "th"];

    $c1 = fmod($num,28);
    $a1 = ($num-$c1)/28;
    $v1 = fmod($a1,79);
    $a2 = ($a1-$v1)/79;
    $c2 = fmod($a2,17);
    $a3 = ($a2-$c2)/17;
    $sy = fmod($a3,3);
    
    $output = "foobar";
    if ($sy>0) {
        $a4 = ($a3-$sy)/3;
        $c3 = fmod($a4,28);
        $a5 = ($a4-$c3)/28;
        $v2 = fmod($a5,79);
        $a6 = ($a5-$v2)/79;
        $c4 = fmod($a6,17);
        $output = hlu($c1,$c1lookup1,$c1lookup2).hlu($v1,$vlookup1,$vlookup2).hlu($c2,$c2lookup1,$c2lookup2).hlu($c3,$c1lookup1,$c1lookup2).hlu($v2,$vlookup1,$vlookup2).hlu($c4,$c2lookup1,$c2lookup2);
    }
    else {
        $output = hlu($c1,$c1lookup1,$c1lookup2).hlu($v1,$vlookup1,$vlookup2).hlu($c2,$c2lookup1,$c2lookup2);
    }
  
    return $output;
}
function hashElfLookup($num) {
    $c1lookup1 = [11, 12, 14, 15, 16, 18, 19, 20, 22, 24, 26, 27, 29, 30, 31, 32, 33, 34, 35, 36, 37, 39, 40];
    $c1lookup2 = ["", "b", "c", "ç", "d", "f", "g", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "y", "ÿ", "z", "sl", "th", "qu"];
    $vlookup1 = [9, 12, 13, 14, 22, 23, 24, 32, 33, 34, 35, 36, 37];
    $vlookup2 = ["a", "o", "ò", "ó", "e", "è", "é", "i", "ì", "í", "u", "ù", "ú"];
    $c2lookup1 = [1, 4, 5, 6, 8, 9, 10, 13, 16, 19, 20, 23, 24, 25, 26, 27, 28, 29, 30, 31, 34, 35];
    $c2lookup2 = ["b", "c", "ç", "d", "f", "g", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "y", "ÿ", "z", "sl", "th", "qu"];
    $c3lookup1 = [1, 3, 4, 6, 7, 8, 11, 13, 16, 17, 20, 21, 22, 23, 24, 25, 26, 27, 30];
    $c3lookup2 = ["", "b", "c", "d", "f", "g", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "y", "ÿ", "z", "th"];

    $c1 = fmod($num,40);
    $a1 = ($num-$c1)/40;
    $v1 = fmod($a1,37);
    $a2 = ($a1-$v1)/37;
    $c2 = fmod($a2,35);
    $a3 = ($a2-$c2)/35;
    $v2 = fmod($a3,37);
    $a4 = ($a3-$v1)/37;
    $sy = fmod($a3,2);
    
    $output = "foobar";
    if ($sy>0) {
        $a5 = ($a3-$sy)/2;
        $c3 = fmod($a5,30);
        $a6 = ($a4-$c3)/30;
        $v3 = fmod($a6,37);
        $output = hlu($c1,$c1lookup1,$c1lookup2).hlu($v1,$vlookup1,$vlookup2).hlu($c2,$c2lookup1,$c2lookup2).hlu($v2,$vlookup1,$vlookup2).hlu($c3,$c3lookup1,$c3lookup2).hlu($v3,$vlookup1,$vlookup2);
    }
    else {
        $output = hlu($c1,$c1lookup1,$c1lookup2).hlu($v1,$vlookup1,$vlookup2).hlu($c2,$c2lookup1,$c2lookup2).hlu($v2,$vlookup1,$vlookup2);
    }
  
    return $output;
}
function hashHumanLookup($num) {
    $c1lookup1 = [13, 15, 17, 19, 20, 22, 23, 24, 27, 29, 31, 33, 34, 36, 37, 39, 42, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57];
    $c1lookup2 = ["", "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "ñ", "p", "qu", "r", "s", "t", "v", "w", "x", "z", "th", "sh", "ng", "st", "sl", "sm", "sp", "str", "thr"];
    $vlookup1 = [2, 3, 4, 5, 6, 7];
    $vlookup2 = ["a", "á","e","i","o","u"];
    $c2lookup1 = [2, 4, 6, 7, 9, 10, 11, 13, 16, 19, 21, 22, 24, 25, 28, 30, 32, 33, 34, 35, 36, 38, 39, 40, 41, 42, 43, 44, 45, 46];
    $c2lookup2 = ["b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "ñ", "p", "qu", "r", "s", "t", "v", "w", "x", "z", "th", "sh", "ng", "st", "sl", "sm", "sp", "str", "thr"];
    $c3lookup1 = [64, 66, 68, 71, 72, 74, 75, 76, 78, 80, 82, 84, 85, 87, 88, 91, 94, 96, 97, 98, 99, 100, 102, 103, 104, 105, 106, 107, 108, 109, 110];
    $c3lookup2 = ["", "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "ñ", "p", "qu", "r", "s", "t", "v", "w", "x", "z", "th", "sh", "ng", "st", "sl", "sm", "sp", "str", "thr"];
    $c4lookup1 = [18, 19, 21, 23, 24, 25, 26, 28, 32, 35, 38, 39, 42, 44, 46, 47, 48, 49, 50, 52, 53, 54, 55, 56, 57];
    $c4lookup2 = ["", "b", "c", "d", "f", "g", "h", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "z", "th", "sh", "ng", "st", "sm", "sp"];

    $c1 = fmod($num,57);
    $a1 = ($num-$c1)/57;
    $v1 = fmod($a1,7);
    $a2 = ($a1-$v1)/7;
    $c2 = fmod($a2,46);
    $a3 = ($a2-$c2)/46;
    $sy = fmod($a3,5);
    
    $a4 = ($a3-$sy)/5;
    $c3 = fmod($a4,110);
    $a5 = ($a4-$c3)/110;
    $v2 = fmod($a3,7);
    $a6 = ($a5-$v2)/7;
    $c4 = fmod($a6,57);
    
    $output = "foobar";
    if ($sy>0) {        
        $output = hlu($c1,$c1lookup1,$c1lookup2).hlu($v1,$vlookup1,$vlookup2).hlu($c2,$c2lookup1,$c2lookup2).hlu($c3,$c3lookup1,$c3lookup2).hlu($v2,$vlookup1,$vlookup2).hlu($c4,$c4lookup1,$c4lookup2);
    }
    else {
         $output = hlu($c1,$c1lookup1,$c1lookup2).hlu($v1,$vlookup1,$vlookup2).hlu($c4,$c4lookup1,$c4lookup2);
    }
  
    return $output;
}

function hashGoblinLookup($num) {
    $c1lookup1 = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26];
    $c1lookup2 = ["", "d", "b", "g", "k", "l", "m", "n", "r", "s", "t", "x", "z", "st", "sp", "sm", "sl", "sn", "str"];
    $vlookup1 = [5, 6, 7, 8, 11, 12, 13, 23, 24, 25, 34, 35];
    $vlookup2 = ["a", "â", "ä", "å", "e", "ê", "ë", "o", "ô", "ö", "u", "û"];
    $c2lookup1 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19];
    $c2lookup2 = ["b", "d", "g", "k", "l", "m", "n", "r", "s", "t", "x", "z", "th", "st", "sp", "sm", "sl", "sn", "str"];
    $c3lookup1 = [24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43];
    $c3lookup2 = ["", "b", "g", "k", "l", "m", "n", "r", "s", "t", "x", "z", "th", "st", "sp", "sm", "sl", "sn", "str"];
    $c4lookup1 = [15, 17, 19, 20, 22, 23, 25, 27, 29, 31, 33, 34, 36, 37, 39, 40, 41];
    $c4lookup2 = ["", "b", "d", "g", "k", "l", "m", "n", "r", "s", "t", "x", "z", "th", "st", "sp", "sm"];

    $c1 = fmod($num,26);
    $a1 = ($num-$c1)/26;
    $v1 = fmod($a1,35);
    $a2 = ($a1-$v1)/35;
    $c2 = fmod($a2,19);
    $a3 = ($a2-$c2)/19;
    $sy = fmod($a3,4);
    
    $a4 = ($a3-$sy)/4;
    $c3 = fmod($a4,43);
    $a5 = ($a4-$c3)/43;
    $v2 = fmod($a3,35);
    $a6 = ($a5-$v2)/35;
    $c4 = fmod($a6,41);
    
    $output = "foobar";
    if ($sy>0) {        
        $output = hlu($c1,$c1lookup1,$c1lookup2).hlu($v1,$vlookup1,$vlookup2).hlu($c2,$c2lookup1,$c2lookup2).hlu($c3,$c3lookup1,$c3lookup2).hlu($v2,$vlookup1,$vlookup2).hlu($c4,$c4lookup1,$c4lookup2);
    }
    else {
         $output = hlu($c1,$c1lookup1,$c1lookup2).hlu($v1,$vlookup1,$vlookup2).hlu($c4,$c4lookup1,$c4lookup2);
    }
  
    return $output;
}

function hlu($num3, $lookup1, $lookup2) {
    $i = 0;
    while ($num3>$lookup1[$i]) {
        $i++;
    }
    return $lookup2[$i];
}

// get the parameters from URL
$str = $_REQUEST["str"];
$lang = $_REQUEST["lang"];
$fill = $_REQUEST["fill"];
$skip = $_REQUEST["skip"];

$indStr = preg_split( "/(\W)/", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

$output = "";
for ($x = 0; $x < count($indStr); $x++) {
    if(in_array($indStr[$x],["\n"])) {
        $output .= $indStr[$x] . "<br />";
    }
    else if(in_array($indStr[$x],[":",";","'",",",".","<",">","?","{","[","}","]","(",")","!","@","#","$","%","^","&","*","-","_","=","+",""," ","\\"])) {
        $output .= $indStr[$x];
    }
    else {
        if($skip=="no") {
            $output .= checkDatabase($indStr[$x], $lang, $fill, $conn);
        }
        else {
            $output .= hashUnknown($indStr[$x], $lang);
        }
    }
}
echo $output == "" ? $str : $output;
$conn->close();
?>
