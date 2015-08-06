<?php
require_once('RecipeParser.php');
require_once('Color.php');
require_once 'Image/GraphViz.php'; 
/*$socle = new Recipe("Test");
$socle->retrieveData();
$socle->createGraph();
*/
//TODO : create an internalisation class w. strings
//TODO : link members and recipes

/*
*Recipe class has ingredients, definitions, technicals  requirements and members
*In this class you will find the project model and few functions to render a project's graph
*You should also give a look at the ProjectParser class 
*/
class Recipe{
	private $objectsToQuery = "[[%RECETTE%]] ";
	private $parametersToQuery = "|?A membre|?Découle du besoin technique|?Besoin technique lié|?A thème|?Ingrédient lié|?Définition liée|?Projet lié";
	private $title;
	private $theme;
	private $father;

	private $definitions;
	private $ingredients;
	private $members;
	private $techReqs;
	private $projects;

	private $found = false;

	function __construct($recipeName = ''){
		$this->title = addslashes($recipeName);
		$this->definitions = array();
		$this->members = array();
		$this->ingredients = array();
		$this->techReqs = array();
		$this->projects = array();
	}
	public function setFound($found){
		$this->found = $found;
	}
	public function isFound(){
		return $this->found;
	}
	public function setTheme($theme){
		$this->theme = $theme;
	}
	public function setFather($father){
		$this->father = $father;
	}
	public function addIngredient($ingredient){
		array_push($this->ingredients,$ingredient);
	}
	public function addDefinition($definition){
		array_push($this->definitions,$definition);
	}
	public function addMember($member){
		array_push($this->members,$member);
	}
	public function addTechReq($techReq){
		array_push($this->techReqs,$techReq);
	}
	public function addProject($project){
		array_push($this->projects,$project);
	}
	public function getQuery(){
		$query = str_replace("%RECETTE%", $this->title, $this->objectsToQuery);
		$query .= $this->parametersToQuery;
		return $query;
	}
	public function getTitle(){
		return $this->title;
	}
	/*
	*Creates a recipe type parser and ask for data from the mediawiki api
	*The query used exists is in this class but you should NOT change it as everything is tight linked
	*@args 
	*@return 
	*/
	public function retrieveData(){
		$mParser = new RecipeParser;
		$mParser->retrieveInfoForObject($this);
	}

	/*
	*Create a graph and fill it with the father (tech req), linked tech reqs, linked projects, ingredients, definitions and members
	*Functional requirement are add as well but what's under rely on the functional requirement class
	* (as technical requirement and  recipes are linked with the project through the func. requirements)
	*@args 
	*@return code for the graph 
	*/
	public function createGraph(){
		$graph = new Image_GraphViz();
		$attributes = array('rankdir'=>"LR");
		$graph->addAttributes($attributes);
		$graph->addNode($this->title);
		foreach ($this->members as $member) {$this->addAndLinkNodeForRemoteObject($graph,$member,"A comme membre", "member");}
		foreach ($this->definitions as $definition) {$this->addAndLinkNodeForRemoteObject($graph,$definition,"A comme définition", "definition");}
		foreach ($this->ingredients as $ingredient) {$this->addAndLinkNodeForRemoteObject($graph,$ingredient,"A comme ingrédient", "ingredient");}
		foreach ($this->projects as $project) {$this->addAndLinkNodeForRemoteObject($graph,$project,"A comme projet", "project");}
		foreach ($this->techReqs as $techReq) {$this->addAndLinkNodeForRemoteObject($graph,$techReq,"Nécessite le besoin technique", "techreq");}
		$this->addAndLinkNodeForRemoteObject($graph, $this->father, "Découle du besoin technique", "techreq");
		$this->linkWithString($graph, $this->theme, "A comme theme", "theme");
		//$graph->image();
		return $graph;
	}
	public function showGraph(){
		$this->createGraph()->image();
	}
	public function getGraphCode(){
		return $this->createGraph()->parse();
	}
	public function retrieveAndGetCode(){
		$this->retrieveData();
		return $this->getGraphCode();
	}

	/*
	*Add first depth instance of the  RemoteObject class
	*@args the $graph we are dealing w. the $remoteObject to render on the graph and the $label that has to be shown on the edge
	*@return
	*/
	public function addAndLinkNodeForRemoteObject($graph, $remoteObject, $label, $type){
		$url = '';
		if($remoteObject != null){
			$args = array();
			if($remoteObject->exists()){
				$url= $remoteObject->getUrl();
				$args['shape'] = 'box';
			}else{
				$url= $remoteObject->getUrl();
				$url = str_replace("index.php/", "index.php/Spécial:AjouterDonnées/".Color::getType($type)."/", $url);
				$args['shape'] = 'dot';
			}
			$args['URL'] = $url;
			$args['color'] = Color::colorNode($type);
			$graph->addNode($remoteObject->getTitle(), $args); 
			$args['URL'] = '';
			$args['label'] = $label;
			$args['color'] = Color::colorEdge($type);
			$graph->addEdge(array($this->title => $remoteObject->getTitle()), $args); 
		}
	}
	public function retrieveAndRender(){
		$this->retrieveData();
		return $this->createGraph();
	}
	/*
	*Link Recipe with a string already in the graph
	*@args the $graph we are dealing w. the $remoteObject to render on the graph and the $label that has to be shown on the edge
	*@return
	*/
	public function linkWithString($graph, $string, $label, $type){
		if($string != null)
		$graph->addEdge(array($this->title => $string), array('label' => $label,'color' => Color::colorEdge($type))); 
	}
	/*	
	public function setDefinitions($resultsArray){
		$this->definitions = $resultsArray["Définition liée"];
	}
	public function setIngredients($resultsArray){
		$this->ingredients = $resultsArray["Ingrédient lié"];
	}
	public function setFuncReqs($funcReqs){
		$this->funcReqs = $funcReqs;
	}
	public function setMembers($members){
		$this->members = $members;
	}*/
}
?>