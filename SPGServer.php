<?php
class SPGServer{
	 private $xyCategoriesMaxAge = 36;
 	private $graphCache = "../../images/SemanticProjectGraph_Cache/";

	 function serveFile() {
	 	echo 'JE USIS LA ';
    // Get filename from GET parameter
    if(isset($_GET['png'])) {
      $filename = @$_GET['png'];
      }
    else {
      return false;
      }
    // Check filename is valid
    if (preg_match('/\\W/',$filename))return false;
    
    $docRoot = __DIR__.'/'.$this->graphCache;
    $file = "$docRoot$filename.png";
    // Check file exists
    if (!file_exists($file)) return false;
    // Get filetime
    $time = @filemtime($file);
    // Get filesize
    $size = @filesize($file);
  
    $etag = md5("$time|$size");
    // Get "Last-Modified"
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
      $oldtime=strtotime(current(explode(';',$_SERVER['HTTP_IF_MODIFIED_SINCE'])));
      }
    // Get "ETag"
    if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
      $oldetag=explode(',',$_SERVER['HTTP_IF_NONE_MATCH']);
      }
    // If either is unchanged the file is not modified.     
    if ( (isset($oldtime) && $oldtime == $time ) ||
       (isset($oldetag) && $oldetag == $etag ) ) {
      header('HTTP/1.1 304 Not Modified');
      header('Date: '.gmdate('D, d M Y H:i:s').' GMT');
      header('Server: PHP');
      header("ETag: $etag");
      return true;
      }
    // Send headers
    header('HTTP/1.1 200 OK');
    header('Date: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Server: PHP');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s',$time).' GMT');
    header('Expires: '.gmdate('D, d M Y H:i:s',$time+$this->xyCategoriesMaxAge).' GMT');
    // Supply the filename that is proposed when saving the file to disk   
    header("Content-Disposition: inline; filename=cat.png");
    header("ETag: $etag");
    header("Accept-Ranges: bytes");
    header("Content-Length: ".(string)(filesize($file)));
    header("Connection: close\n");
    header("Content-Type: image/png");
    // Send file
    $h = fopen($file, 'rb');
    fpassthru($h);
    fclose($h);
    return true;
  }
}
?>