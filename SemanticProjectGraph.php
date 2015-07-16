<?php

/**
 *
 * @author Hugo Djemaa <hugo.djemaa@gmail.com>
 * @version 0.5.0
 * @link https://github.com/realsoc/SemanticProjectGraph
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
 
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is reference.
 */
if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
        die( -1 );
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
	return $mProject->retrieveAndRender();
	//testing:     
	//return "<pre>".$dottext."</pre>";
}

function SemanticRecipeGraphFunction_Render( $parser,$param1 = '') {
	$mProject = new Recipe($param1);
	return $mProject->retrieveAndRender();
	//testing:
	//return "<pre>$hgtext</pre>";
}

function SemanticTechReqGraphFunction_Render( $parser, $param1 = '') {
	$mProject = new TechnicalRequirement($param1);
	return $mProject->retrieveAndRender();
}
?>
