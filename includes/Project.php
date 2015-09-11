<?php
require_once('ProjectParser.php');
require_once('FunctionalRequirement.php');
require_once('Color.php');
require_once 'Image/GraphViz.php'; 
/*$socle = new Project("SOCLE LAAS15");
$socle->retrieveData();
$socle->createGraph();*/

//TODO : create an internalisation class w. strings
//TODO : link members and recipes

/*
*Project class has ingredients, definitions, functional requirements and members
*In this class you will find the project model and few functions to render a project's graph
*You should also give a look at the ProjectParser class 
*/
class Project{
	private $objectsToQuery = "[[%PROJET%]] OR [[-Has subobject::%PROJET%]] OR [[Type::RecetteInBT]] [[Projet lié::%PROJET%]] OR [[Type::BTInBT]] [[Projet lié::%PROJET%]] ";
	private $parametersToQuery = "|?A membre|?Contenu|?Type|?Ingrédient lié|?Définition liée|?Recette liée|?Besoin non fonctionnel lié|?ListeBF";
	private $title;
	private $definitions;
	private $ingredients;
	private $funcReqs;
	private $members;
	private $nonFuncReqs;		
	private $found = false;


	function __construct($projectName = ''){
		$this->title = htmlspecialchars_decode($projectName, ENT_QUOTES);
		$this->definitions = array();
		$this->members = array();
		$this->ingredients = array();
		$this->funcReqs = array();
		$this->nonFuncReqs = array();
	}
	public function setFound($found){
		$this->found = $found;
	}
	public function isFound(){
		return $this->found;
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
	public function addNonFuncReq($nonFuncReq){
		array_push($this->nonFuncReqs,$nonFuncReq);
	}
	public function setFuncReqs($funcsReqTree){
		$this->funcReqs = $funcsReqTree;
	}
	public function addTechToFunc($techReq, $funcReqTitle){
		$this->funcReqs[$funcReqTitle]->addTechReq($techReq);
	}

	public function getFuncReqs(){
		return $this->funcReqs;
	}
	public function getQuery(){
		$query = str_replace("%PROJET%", $this->title, $this->objectsToQuery);
		$query .= $this->parametersToQuery;
		return $query;
	}
	public function getFuncReqByTitle($title){
		$ret = null;
		foreach($this->funcReqs as $funcReq){
			if(strcmp($funcReq->getTitle(), $title)){
				$ret = $funcReq;
				break;
			}
		}
		return $ret;
	}
	public function getTitle(){
		return $this->title;
	}
	/*
	*Creates a project type parser and ask for data from the mediawiki api
	*The query used exists is in this class but you should NOT change it as everything is tight linked
	*@args 
	*@return 
	*/
	public function retrieveData(){
		$mParser = new ProjectParser;
		$mParser->retrieveInfoForObject($this);
	}
	/*
	*Add and link recipe with the corresponding technical requirement in a functional requirement (the one where exists the func. requirement)
	*The functional requirement MUST already exists in the functional requirement 
	*You only need to specify the title of the technical requirement
	*@args $recipe to add (RemoteObject type) $title of the Technical requirement to link with
	*@return 
	*/
	public function addRecipeToBF($recipe, $techReqTitle){
		foreach ($this->funcReqs as $funcReq) { 
			if($funcReq->hasTechReqByTitle($techReqTitle)){
				$funcReq->linkRecipeWithTechReqTitle($recipe, $techReqTitle);
				break;
			}
		}
	}
	public function retrieveAndGetCode(){
		$this->retrieveData();
		return $this->getGraphCode();
	}
	/*
	*Create a graph and fill it with the ingredients, definitions and members
	*Functional requirement are add as well but what's under rely on the functional requirement class
	* (as technical requirement and  recipes are linked with the project through the func. requirements)
	*@args 
	*@return code for the graph 
	*/
	public function createGraph(){
		$graph = new Image_GraphViz(true,array( 'size' => "17,17","ratio" => "true"));
		$attributes = array('rankdir'=>"LR");
		$graph->addAttributes($attributes);
		$graph->addNode($this->title);
		//var_dump($this->nonFuncReqs);
		foreach($this->nonFuncReqs as $nonFuncReq){$this->addAndLinkNodeForRemoteObject($graph, $nonFuncReq, "A comme besoin non fonctionnel", "nonfuncreq");}
		foreach ($this->members as $member) {$this->addAndLinkNodeForRemoteObject($graph,$member,"A comme membre", "member");}
		foreach ($this->definitions as $definition) {$this->addAndLinkNodeForRemoteObject($graph,$definition,"A comme définition", "definition");}
		foreach ($this->ingredients as $ingredient) {$this->addAndLinkNodeForRemoteObject($graph,$ingredient,"A comme ingrédient" , "ingredient");}
		foreach ($this->funcReqs as $funcReq) {$this->addAndLinkNodeForFuncReq($graph,$funcReq);}
		return $graph;
		//$graph->image();
		//$graph->image(); 
	}

	public function showGraph(){
		$this->createGraph()->image();
	}
	public function getGraphCode(){
		return $this->createGraph()->parse();
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
	/*
	*create functional requirements and call the method graphyourself in Functional Requirement which take flame
	*@args $graph and $functionalRequirement
	*
	*/
	public function addAndLinkNodeForFuncReq($graph, $funcReq){
		$title = $funcReq->getTitle();
		$graph->addNode($title, array( 'shape' => 'box', Color::colorNode('funcreq')) ); 
		$graph->addEdge(array($this->title => $title), array('label' => "A comme besoin fonctionnel",'color' => Color::colorEdge('funcreq')));
		$graph = $funcReq->FRGraphYourself($graph);
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