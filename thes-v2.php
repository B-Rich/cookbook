<?php

define("PATH", "/Users/ogs22/CMEP/cookbook/glossary/");

function mkazdir() {
    foreach (range('a', 'z') as $letter) {
        if (!file_exists(PATH.$letter)) {
            mkdir(PATH.$letter);
        }
    }
    mkdir(PATH."other");
}

function cleanup($data='') {
    $config = array(
       // 'indent' => false,
        'output-xhtml' => true,
     //   'wrap' => 400,
        'numeric-entities' => true
    );
    $config['char-encoding'] = 'utf8';
    $config['input-encoding'] = 'utf8';
    $config['output-encoding'] = 'utf8';

    $data = trim($data);
    $data = trim($data,'"');
	$data = str_replace('\r\n',' ',$data);
	$slh = '\"';
	$data = str_replace($slh, '"', $data);
	$data = trim($data);
	$tidy = new tidy();
    $tidy->parseString($data, $config, "utf8");
    $tidy->repairString($data, $config, "utf8");
    $body = $tidy->Body();
    $string = $body->value;
    $string = str_replace("<body>", "", $string);
    $string = str_replace("</body>", "", $string);
    return trim($string);
}

function pdmd($file) {
//    'pandoc -f html -t markdown hello.html'
    $cmd = 'pandoc -f latex -t markdown \''.PATH.$file.'.tex\' -o \''.PATH.$file.'.md\'';
    exec($cmd);
}

function pdhtml($file) {
    $cmd = 'pandoc -f latex -t html \''.PATH.$file.'.tex\' -o \''.PATH.$file.'.html\'';
    exec($cmd); 
}


function makeName($title) {
    $name = trim(strtolower($title));
    $name = preg_replace("/\\\'/","_",$name);
    $name = preg_replace("/\'/","_",$name);
    $name = preg_replace("/\s/","_",$name);
    $name = str_replace('"',"_",$name);
    $name = str_replace('*',"_",$name);
    $name = str_replace('/',"_",$name);
    $name = str_replace(':',"_",$name);
    $name = str_replace('<',"_",$name);
    $name = str_replace('>',"_",$name);
    $name = str_replace('&',"_",$name);
    $name = str_replace(';',"_",$name);
    $name = str_replace('?',"_",$name);
    $name = str_replace('|',"vbar",$name);
    $name = str_replace("__","_",$name);
    return $name;
}


function getDirAlpha($name) {
    $fl = $name[0];
    if (preg_match('/[a-z]/', $fl) == 1) {
        return $fl;
    } else {
        return "other";
    }
}

function checkDupe(&$name,$depth=1) {
    if (file_exists(PATH.$name.".md")) {
        $name = $name."I";
        checkDupe($name,$depth++);
    }
}


$link = mysqli_connect("localhost","root","tyuhbvcf","thes3") or die("Error " . mysqli_error($link));
$query = 'select names.name,names.concept_no,source from entries join names where names.concept_no=entries.concept_no and lang = "en" group by names.concept_no' or die("Error" . mysqli_error($link));
$result = $link->query($query);

echo mysqli_num_rows($result);

mkazdir();

while($row = mysqli_fetch_assoc($result)) {
  
  	$def = utf8_encode($row['source']);
  	$clean = cleanup($def);
//    $md = md($clean);
    $name = makeName(utf8_encode($row['name']));
    checkDupe($name);
    echo $row['name']."::";
    echo $row['concept_no']."::";
    echo $name.".md\n";
    $dir = getDirAlpha($name)."/";
//    file_put_contents(PATH.$name.".md", $md);
    file_put_contents(PATH.$dir.$name.".db", $row['source']);
    file_put_contents(PATH.$dir.$name.".tex", $def);
    //file_put_contents(PATH.$name.".html", $clean);

    pdmd($dir.$name);
    pdhtml($dir.$name); 

} 

//$yaml = yaml_emit($x);

//echo $yaml;













