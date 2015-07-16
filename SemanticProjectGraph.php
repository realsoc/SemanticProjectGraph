<?php

/**
 *
 * @author Hugo Djemaa <hugo.djemaa@gmail.com>
 * @version 0.5.0
 * @link https://github.com/realsoc/SemanticProjectGraph
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
 
 $dotPath = "/usr/bin/dot";
 $graphCache = "../../images/SemanticProjectGraph_Cache/";
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is reference.
 */
if( !defined( 'MEDIAWIKI' ) ) {
         require_once 'xyCategoryGraph.php';
  		// Serve the PNG image
  		$server = new SPGServer();
  		if ($server->serveFile()) die();
  		header("HTTP/1.1 404 Not Found");
  		die("<H1>404 Not Found </H1>");
}

# Define a setup function


// Extension credits that will show up on Special:Version    
$wgExtensionCredits['parserhook'][] = array(
		'path' 			=> __FILE__,
        'name'         => 'SemanticProjectGraph',
        'version'      => '0.5',
        'author'       => array('Hugo Djemaa'), 
        'url'          => 'https://github.com/realsoc/SemanticProjectGraph',
        'description'  => 'This extension render graphs for three type of objects (described here : http://smw.learning-socle.org');
$dir = __DIR__ . '/';
include_once($dir.'includes/Project.php');
include_once($dir.'includes/TechnicalRequirement.php');
include_once($dir.'includes/Recipe.php');

$wgHooks['LanguageGetMagic'][]       = 'SemanticProjectGraph_Magic';
$wgHooks['ParserFirstCallInit'][] = 'SemanticProjectGraphParserFunction_Setup';


function SemanticProjectGraph_Magic( &$magicWords, $langCode ) {
        # Add the magic word
        # The first array element is case sensitive, in this case it is not case sensitive
        # All remaining elements are synonyms for our parser function
        $magicWords['projectgraph'] = array( 0, 'projectgraph');
        $magicWords['recipegraph'] = array( 0, 'recipegraph');
        $magicWords['techreqgraph'] = array( 0, 'techreqgraph');
        # unless we return true, other parser functions extensions won't get loaded.
        return true;
}
function SemanticProjectGraphParserFunction_Setup(&$parser) {
        $parser->setFunctionHook( 'projectgraph', 'SemanticProjectGraphFunction_Render' );
        $parser->setFunctionHook( 'recipegraph', 'SemanticRecipeGraphFunction_Render' );
        $parser->setFunctionHook( 'techreqgraph', 'SemanticTechReqGraphFunction_Render' );
        return true;
}

function SemanticProjectGraphFunction_Render( $parser, $param1 = '') {
	$mProject = new Project($param1);
	$dotStr = $mProject->retrieveAndRender();
	doDot($param1, $dotStr);
	$ret = htmlForImage($param1);	
	if($ret == null){
		$ret = '<h1>SARACE</h1>';
	}
	return array($ret, 'isHTML' => true);;
	//testing:     
	//return "<pre>".$dottext."</pre>";
}

function SemanticRecipeGraphFunction_Render( $parser,$param1 = '') {
	$mProject = new Recipe($param1);
	$dotStr = $mProject->retrieveAndRender();
	doDot($param1, $dotStr);
	$ret = htmlForImage($param1);	
	if($ret == null){
		$ret = '<h1>SARACE</h1>';
	}
	return $ret;
	//testing:
	//return "<pre>$hgtext</pre>";
}

function SemanticTechReqGraphFunction_Render( $parser, $param1 = '') {
	$mProject = new TechnicalRequirement($param1);
	$dotStr = $mProject->retrieveAndRender();
	doDot($param1, $dotStr);
	$ret = htmlForImage($param1);
	if($ret == null){
		$ret = '<h1>SARACE</h1>';
	}
	return $ret;
}
function doDot( $title, $dot ) { 
	global $graphCache, $dotPath;
    $md5 = md5($title);
    $docRoot = __DIR__.'/'.$graphCache;
    $fileDot = "$docRoot$md5.dot";
    $fileMap = "$docRoot$md5.map";
    $fileSvg = "$docRoot$md5.svg";

    file_put_contents1($fileDot, $dot);
    $result = shell_exec("$dotPath -Tsvg -o$fileSvg <$fileDot");
    $map = shell_exec("$dotPath -Tcmap -o$fileMap <$fileDot");
}
  /**
   * @brief Outputs the image to the OutputPage object.
   *
   * @param title to generate md5 for filename
   */
  function htmlForImage( $title ) {
	global $graphCache, $wgScriptPath;
	$script = "SemanticProjectGraph.php";
	$html= '';
    $docRoot = __DIR__.'/'.$graphCache;
    $md5 = md5($title);
    $fileMap = "$docRoot$md5.map";
    if (file_exists($fileMap)) {
      $map = file_get_contents1($fileMap); 
      $URLsvg =  "$wgScriptPath/extensions/SemanticProjectGraph/$script?svg=$md5";
      if (file_exists($URLsvg)){
      	$html = "<DIV><IMG src=\"$URLsvg\" usemap=\"#map1\" alt=\"$title\"><MAP name=\"map1\">$map</MAP>";
      	$html .= "</DIV>";
      }

      return $html;
      }
    else {
      return null;
      }
    }
  /**
   * @brief Writes binary string to file.
   *
   * @param $n file name
   * @param $d binary string
   *
   * @return success
   */
  function file_put_contents1($n,$d) {
    $f=@fopen($n,"wb") or die(print_r(error_get_last(),true));
    if (!$f) {
      return false;
      } 
    else {
      fwrite($f,$d);
      //echo 'ici : '.$f.'<br>';
      fclose($f);
      return true;
      }
    }    

  /**
   * @brief Reads binary string from file.
   *
   * @param $n file name
   *
   * @return binary string (or false if failed)
   */
  function file_get_contents1($n) {

    $f=@fopen($n,"rb") or die(print_r(error_get_last(),true));
    if (!$f) {
      return false;
      } 
    else {
      $s=filesize($n);
      $d=false;
      if ($s) $d=fread($f, $s) ; 
      fclose($f);
      return $d;
      }
    }    
?>
