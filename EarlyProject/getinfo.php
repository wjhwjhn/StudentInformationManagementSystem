<?php
error_reporting(0);
date_default_timezone_set("Asia/Shanghai");
$mysqli = @new mysqli("127.0.0.1", "root", "root", 'students');
if ($mysqli->connect_errno) {
    echo 'database fail';
    die();
}
if (!isset($_GET['method'])) {
    echo 'method fail';
    die();
}
$arg = 0;
if (isset($_GET['arg'])) {
    $arg = $_GET['arg'];
}
$arg2 = 0;
if (isset($_GET['arg2'])) {
    $arg2 = $_GET['arg2'];
}
$method = $_GET['method'];

$RetData['error'] = 0;

switch ($method) {
    case "init":
    {
        $RetData['getteachers'] = getteachers();
        $RetData['getclass'] = getclass();
        break;
    }
    case "getteachers":
        $RetData['getteachers'] = getteachers();
        break;
    case "getclass":
        $RetData['getclass'] = getclass();
        break;
    case "getstudents":
        $RetData['getstudents'] = getstudents($arg);
        break;
    case "getstudentdata":
        $RetData['getstudentdata'] = getstudentdata($arg, $arg2);
        break;
    case "getclasstudentdata":
        $RetData['getstudentdata'] = getclasstudentdata($arg, $arg2);
        break;
    case "signtext":
        $RetData['signstudentstext'] = signstudentstext();
        break;
    case "setphoto":
        $RetData['setphoto'] = setphoto($arg, $arg2);
        break;
}
echo json_encode($RetData);

function setphoto($url, $sid)
{
    global $mysqli;
    $query = "UPDATE students SET photourl = ? WHERE sid = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('si', $url, $sid);
        $stmt->execute();
        $stmt->store_result();
        $result['res'] = $stmt->affected_rows;
        $stmt->close();
    }
    return $result;
}

function getteachers()
{
    global $mysqli;
    $query = "SELECT tid,name FROM teachers";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($tid, $name);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['tid'] = $tid;
            $result[$i]['name'] = $name;
            $i++;
        }
        $stmt->close();
    }
    return $result;
}

function getclass()
{
    global $mysqli;
    $query = "SELECT cid,name FROM class";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($cid, $name);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['cid'] = $cid;
            $result[$i]['name'] = $name;
            $i++;
        }
        $stmt->close();
    }
    return $result;
}

function getstudents($cid)
{
    global $mysqli;
    $query = "SELECT sid,name,stopenglish,englishscore,km1name,km1score,km1type,km2name,km2score,km2type,mainname,mainscore,maintype,mainteacher,secondname,secondscore,secondtype,secondteacher,photourl FROM students WHERE cid = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('i', $cid);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($sid, $name, $stopenglish, $englishscore, $km1name, $km1score, $km1type, $km2name, $km2score, $km2type, $mainname, $mainscore, $maintype, $mainteacher, $secondname, $secondscore, $secondtype, $secondteacher, $photourl);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['sid'] = $sid;
            $result[$i]['name'] = $name;
            $result[$i]['photourl'] = $photourl;
            $baseinfo = "英语 ";
            $baseinfo .= $stopenglish == 'T' ? " 停考 " : " 未停考 ";
            $baseinfo .= $englishscore . "分\n";
            $baseinfo .= "停考科目1：" . $km1name . " " . $km1type . " " . $km1score . "分\n";
            if ($km2name != "")
            {
                $baseinfo .= "停考科目2：" . $km2name . " " . $km2type . " " . $km2score . "分\n";
            }
            $baseinfo .= "主考科目：" . $mainname . " " . $maintype . " " . $mainscore . "分 任课教师：" . $mainteacher . "\n";
            if ($secondname != "")
            {
                $baseinfo .= "次考科目：" . $secondname . " " . $secondtype . " " . $secondscore . "分 任课教师：" . $secondteacher . "\n";
            }
            $result[$i]['baseinfo'] = $baseinfo;
            $i++;
        }
        $stmt->close();
    }
    return $result;
}

function getstudentdata($sid, $tid)
{
    global $mysqli;
    if ($tid == 1000) {
        $query = "SELECT students.`name` AS sname, teachers.`name` AS tname, files.text, timestamp, filename, uploadurl FROM files,students,teachers WHERE files.sid = ? AND students.sid = files.sid AND teachers.tid = files.tid";
    }
    else{
        $query = "SELECT students.`name` AS sname, teachers.`name` AS tname, files.text, timestamp, filename, uploadurl FROM files,students,teachers WHERE files.sid = ? AND files.tid = ? AND students.sid = files.sid AND teachers.tid = files.tid";
    }
    if ($stmt = $mysqli->prepare($query)) {
        if ($tid == 1000)
        {
            $stmt->bind_param('i', $sid);
        }
        else
        {
            $stmt->bind_param('ii', $sid, $tid);
        }
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($sname, $tname, $filetext, $timestamp, $filename, $uploadurl);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['sname'] = $sname;
            $result[$i]['tname'] = $tname;
            $result[$i]['filetext'] = $filetext;
            $result[$i]['timestamp'] = date('Y-m-d H:i:s', $timestamp);
            $result[$i]['filename'] = $filename;
            $result[$i]['uploadurl'] = $uploadurl;
            $i++;
        }
        $stmt->close();
    }
    return $result;
}

function getclasstudentdata($cid, $tid)
{
    global $mysqli;
    if ($tid == 1000) {
        $query = "SELECT students.`name` AS sname, teachers.`name` AS tname, files.text, timestamp, filename, uploadurl FROM files,students,teachers WHERE students.cid = ? AND students.sid = files.sid AND teachers.tid = files.tid";
    }
    else{
        $query = "SELECT students.`name` AS sname, teachers.`name` AS tname, files.text, timestamp, filename, uploadurl FROM files,students,teachers WHERE students.cid = ? AND students.tid = ? AND students.sid = files.sid AND teachers.tid = files.tid";
    }
    if ($stmt = $mysqli->prepare($query)) {
        if ($tid == 1000)
        {
            $stmt->bind_param('i', $cid);
        }
        else
        {
            $stmt->bind_param('ii', $cid, $tid);
        }

        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($sname, $tname, $filetext, $timestamp, $filename, $uploadurl);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['sname'] = $sname;
            $result[$i]['tname'] = $tname;
            $result[$i]['filetext'] = $filetext;
            $result[$i]['timestamp'] = date('Y-m-d H:i:s', $timestamp);
            $result[$i]['filename'] = $filename;
            $result[$i]['uploadurl'] = $uploadurl;
            $i++;
        }
        $stmt->close();
    }
    return $result;
}


function signstudentstext()
{
    global $mysqli;
    if (isset($_POST['sid'])) {
        $sid = $_POST['sid'];
    }
    if (isset($_POST['tid'])) {
        $tid = $_POST['tid'];
    }
    if (isset($_POST['text'])) {
        $text = $_POST['text'];
    }
    if (isset($_POST['filename'])) {
        $filename = $_POST['filename'];
    }
    if (isset($_POST['uploadurl'])) {
        $uploadurl = $_POST['uploadurl'];
    }
    $query = "INSERT INTO files(tid,sid,text,timestamp, filename, uploadurl) VALUES(?,?,?,?,?,?)";
    if ($stmt = $mysqli->prepare($query)) {
        $nowtime = time();
        $stmt->bind_param('iisiss', $tid, $sid, $text, $nowtime, $filename, $uploadurl);
        $stmt->execute();
        $stmt->store_result();
        $result['res'] = $stmt->affected_rows;
        $stmt->close();
    }
    return $result;
}

?>