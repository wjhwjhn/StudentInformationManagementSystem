<?php
//error_reporting(0);
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
session_start();
$arg = 0;
if (isset($_GET['arg'])) {
    $arg = $_GET['arg'];
}
$arg2 = 0;
if (isset($_GET['arg2'])) {
    $arg2 = $_GET['arg2'];
}
$arg3 = 0;
if (isset($_GET['arg3'])) {
    $arg3 = $_GET['arg3'];
}
$method = $_GET['method'];

$RetData['error'] = 0;

switch ($method) {
    case "init":
        $RetData['init'] = getinit();
        break;
    case "login":
        $RetData['login'] = setlogin($arg);
        break;
    case "getteachers":
        $RetData['getteachers'] = getteachers();
        break;
    case "getclass":
        $RetData['getclass'] = getclass();
        break;
    case "getstudentdata":
        if ($arg != 1000) $RetData['getstudentexam'] = getstudentexam($arg);
        $RetData['getstudentdata'] = getstudentdata($arg, $arg2);
        break;
    case "getclasstudentdata":
        $RetData['getstudentdata'] = getclasstudentdata($arg, $arg2, $arg3);
        break;
    case "signtext":
        $RetData['signstudentstext'] = signstudentstext();
        break;
    case "setphoto":
        $RetData['setphoto'] = setphoto($arg, $arg2);
        break;
    case "getteacherstudents":
        $RetData['getteacherstudents'] = getteacherstudents();
        break;
    case "deletetext":
        $RetData['deletetext'] = deletetext($arg);
        break;
}

echo json_encode($RetData);

function deletetext($fid)
{
    global $mysqli;
    $query = "DELETE FROM files WHERE fid = ? AND tid = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $nowtime = time();
        $stmt->bind_param('ii', $fid, $_SESSION['tid']);
        $stmt->execute();
        $stmt->store_result();
        $result['res'] = $stmt->affected_rows;
        $stmt->close();
    }
    return $result;
}

function getteacherstudents()
{
    global $mysqli;
    $query = "SELECT sid,class.cid,students.name,class.name AS classname,photourl,students.type FROM students,class WHERE sid IN (SELECT sid FROM score WHERE teacher LIKE CONCAT('%', ? ,'%')) AND class.cid = students.cid";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('s', $_SESSION['name']);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($sid, $cid, $name, $classname, $photourl, $type);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['sid'] = $sid;
            $result[$i]['cid'] = $cid;
            $result[$i]['classname'] = $classname;
            $result[$i]['name'] = $name;
            $result[$i]['photourl'] = $photourl;
            $result[$i]['type'] = $type;
            $i++;
        }
        $stmt->close();
    }
    return $result;
}

function setlogin($tid)
{
    global $mysqli;
    $query = "SELECT tid,name FROM teachers WHERE tid = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('i', $tid);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($tid, $name);
        if ($stmt->num_rows == 1)
        {
            if ($stmt->fetch())
            {
                $_SESSION['tid'] = $tid;
                $_SESSION['name'] = $name;
                $result['error'] = 0;
                $result['tid'] = $tid;
                $result['name'] = $name;
            }
            else
            {
                $result['error'] = -2;
            }
        }
        else
        {
            $result['error'] = -1;
        }
        $stmt->close();
    }
    return $result;
}

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

function getinit()
{
    global $RetData;
    if (!isset($_SESSION['tid']))
    {
        $RetData['error'] = -1;
        $RetData['getteachers'] = getteachers();
        return;
    }
    $result['name'] = $_SESSION['name'];
    $result['tid'] = $_SESSION['tid'];
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

function getstudentexam($sid)
{
    global $mysqli;
    $query = "SELECT sid,type,lasttype,score,teacher,source,kmname,examtime FROM score WHERE sid = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('i', $sid);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($sid, $type, $lasttype, $score, $teacher, $source, $kmname, $examtime);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['sid'] = $sid;
            $result[$i]['type'] = $type;
            $result[$i]['lasttype'] = $lasttype;
            $result[$i]['score'] = $score;
            $result[$i]['teacher'] = $teacher;
            $result[$i]['source'] = $source;
            $result[$i]['kmname'] = $kmname;
            $result[$i]['examtime'] = $examtime;
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
        $query = "SELECT files.fid,students.`name` AS sname, teachers.`name` AS tname, files.text, timestamp, filename, uploadurl FROM files,students,teachers WHERE files.sid = ? AND students.sid = files.sid AND teachers.tid = files.tid";
    }
    else{
        $query = "SELECT files.fid,students.`name` AS sname, teachers.`name` AS tname, files.text, timestamp, filename, uploadurl FROM files,students,teachers WHERE files.sid = ? AND files.tid = ? AND students.sid = files.sid AND teachers.tid = files.tid";
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
        $stmt->bind_result($fid,$sname, $tname, $filetext, $timestamp, $filename, $uploadurl);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['fid'] = $fid;
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

function getclasstudentdata($cid, $tid, $type)
{
    global $mysqli;
    if ($tid == 1000) {
        $query = "SELECT files.fid,students.`name` AS sname, teachers.`name` AS tname, files.text, timestamp, filename, uploadurl FROM files,students,teachers WHERE students.cid = ? AND students.type = ? AND students.sid = files.sid AND teachers.tid = files.tid";
    }
    else{
        $query = "SELECT files.fid,students.`name` AS sname, teachers.`name` AS tname, files.text, timestamp, filename, uploadurl FROM files,students,teachers WHERE students.cid = ? AND students.type = ? AND students.tid = ? AND students.sid = files.sid AND teachers.tid = files.tid";
    }
    if ($stmt = $mysqli->prepare($query)) {
        if ($tid == 1000)
        {
            $stmt->bind_param('ii', $cid, $type);
        }
        else
        {
            $stmt->bind_param('iii', $cid, $type, $tid);
        }

        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($fid, $sname, $tname, $filetext, $timestamp, $filename, $uploadurl);
        $i = 0;
        while ($stmt->fetch()) {
            $result[$i]['fid'] = $fid;
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
        $text = htmlentities($_POST['text']);
        $text = str_replace("\n","<br>",$text);
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