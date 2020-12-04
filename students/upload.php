<?php
$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
$uploaded_ext  = substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1);
$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
$uploaded_type = $_FILES[ 'uploaded' ][ 'type' ];
$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];
$target_path   =  'uploads/';
$target_file   =  md5( uniqid() . $uploaded_name ) . '.' . $uploaded_ext;
$temp_file     = ( ( ini_get( 'upload_tmp_dir' ) == '' ) ? ( sys_get_temp_dir() ) : ( ini_get( 'upload_tmp_dir' ) ) );
$temp_file    .= DIRECTORY_SEPARATOR . md5( uniqid() . $uploaded_name ) . '.' . $uploaded_ext;

if( ( strtolower( $uploaded_ext ) == 'jpg' || strtolower( $uploaded_ext ) == 'jpeg' || strtolower( $uploaded_ext ) == 'png' ) &&
    ( $uploaded_type == 'image/jpeg' || $uploaded_type == 'image/png' ) &&
    getimagesize( $uploaded_tmp ) )
{
    if( $uploaded_type == 'image/jpeg' )
    {
        $img = imagecreatefromjpeg($uploaded_tmp);
        imagejpeg($img, $temp_file, 100);
    }
    else{
        $img = imagecreatefrompng($uploaded_tmp);
        imagepng($img, $temp_file, 9);
    }
    imagedestroy($img);
    if(rename($temp_file, (getcwd().DIRECTORY_SEPARATOR.$target_path.$target_file))) {
        $url = "http://domain/uploads/{$target_file}";
        $RetData['initialPreview'] = $url;
        $RetData['initialPreviewConfig'] =  [
            ['type' => 'image', 'caption' => "{$uploaded_name}",'previewAsData' => true, 'size' => $uploaded_size, 'url' => "del.php", 'key' =>  $target_file],
        ];
        $RetData['append'] = true;
    }
    else {
        $RetData['error'] = "系统内部错误，请刷新后再试";
    }
    if( file_exists( $temp_file ) )
        unlink( $temp_file );
}
else {
    $RetData['error'] = "不被允许的的文件扩展名，目前允许只上传jpg和png文件，请刷新后再试";
}
echo json_encode($RetData);
?>
