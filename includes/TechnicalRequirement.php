<?php
require_once('TechReqParser.php');
require_once('Color.php');
require_once 'Image/GraphViz.php'; 
/*$mw = new TechnicalRequirement("Installer et configurer MediaWiki");
$mw->retrieveData();
$mw->createGraph();
*/
//TODO : create an internalisation class w. strings
//TODO : link members and recipes

/*
*Technical requirement class has ingredients, definitions, functional requirements and members
*In this class you will find the tech req model and few functions to render a tech req's graph
*You should also give a look at the TechReqParser class 
*/
class TechnicalRequirement{
	private $objectsToQuery = "[[Category:Besoin technique]] ";
	private $parametersToQuery = "|?A thème|?Recette liée|?Définition liée|?Ingrédient lié";
	private $title;
	private $recipes;
	private $definitions;
	private $ingredients;
	private $theme;

	function __construct($techReqName = ''){
		$this->title = $techReqName;
		$this->definitions = array();
		$this->ingredients = array();
		$this->recipes = array();
	}
	public function addIngredient($ingredient){
		array_push($this->ingredients,$ingredient);
	}
	public function addRecipe($recipe){
		array_push($this->recipes,$recipe);
	}
	public function addDefinition($definition){
		array_push($this->definitions,$definition);
	}
	public function setTheme($theme){
		$this->theme = $theme;
	}
	public function getQuery(){
		$query = str_replace("%BT%", $this->title, $this->objectsToQuery);
		$query .= $this->parametersToQuery;
		return $query;
	}
	public function getTitle(){
		return $this->title;
	}
	/*
	*Creates a technical requirement type parser and ask for data from the mediawiki api
	*The query used exists is in this class but you should NOT change it as everything is tight linked
	*@args 
	*@return 
	*/
	public function retrieveData(){
		$mParser = new TechReqParser;
		$mParser->retrieveInfoForObject($this);
	}
	public function retrieveAndRender(){
		$this->retrieveData();
		return $this->createGraph();
	}
	/*
	*Create a graph and fill it with the ingredients, theme recipes  and definitions 
	*@args 
	*@return code for the graph 
	*/
	public function createGraph(){
		$graph = new Image_GraphViz();
		$attributes = array('rankdir'=>"LR");
		$graph->addAttributes($attributes);
		$graph->addNode($this->title);
		$this->linkWithString($graph,$this->theme,"A comme thème");
		foreach ($this->definitions as $definition) {$this->addAndLinkNodeForRemoteObject($graph,$definition,"A comme définition", "definition");}
		foreach ($this->ingredients as $ingredient) {$this->addAndLinkNodeForRemoteObject($graph,$ingredient,"A comme ingrédient", "ingredient");}
		foreach ($this->recipes as $recipe) {
			if($recipe instanceof RemoteRecipe){
				$this->addAndLinkNodeForRemoteRecipe($graph,$recipe);
			}elseif($recipe instanceof RemoteObject){
				$this->addAndLinkNodeForRemoteObject($graph,$recipe, "A comme recette");
			}
		}
		return $graph->parse();
		//$graph->image(); 
	}
	public function linkWithString($graph, $string, $label){
		if($string != null)
		$graph->addEdge(array($this->title => $string), array('label' => $label,'color' => 'blue')); 
	}
	/*
	*Add first depth instance of the  RemoteObject class
	*@args the $graph we are dealing w. the $remoteObject to render on the graph and the $label that has to be shown on the edge
	*@return
	*/
	public function addAndLinkNodeForRemoteObject($graph, $remoteObject, $label, $type){
		$url = '';
		if($remoteObject != null){
			if($remoteObject->exists()){
				$url= $remoteObject->getUrl();
			}
			$args = array();
			$args['URL'] = $url;
			$args['shape'] = 'box';
			$args['color'] = Color::colorNode($type);
			$graph->addNode($remoteObject->getTitle(), $args); 
			$args['URL'] = '';
			$args['label'] = $label;
			$args['color'] = Color::colorEdge($type);
			$graph->addEdge(array($this->title => $remoteObject->getTitle()), $args); 
		}
	}

	public function addAndLinkNodeForRemoteRecipe($graph, $remoteObject){
		$url = '';
		if($remoteObject != null){
			if($remoteObject->exists()){
				$url= $remoteObject->getUrl();
			}
			$graph->addNode($remoteObject->getTitle(), array('URL' => $url, 'shape' => 'box', 'color' =>Color::colorNode('recipe')) ); 
			$graph->addEdge(array($this->title => $remoteObject->getTitle()), array('label' => "A comme recette",'color' => Color::colorEdge('recipe'))); 
			foreach ($remoteObject->getMembers() as $member) {
				$graph->addEdge(array($remoteObject->getTitle() => $member->getTitle()), array('label' => "A comme membre",'color' => Color::colorEdge('member'))); 	
			}
		}
	}

	/*	
	public function setDefinitions($resultsArray){
		$this->definitions = $resultsArray["Définition liée"];
	}
	public function setIngredients($resultsArray){
		$this->ingredients = $resultsArray["Ingrédient lié"];
	}
	*/
}
?>