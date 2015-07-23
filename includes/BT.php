<?php 
class BT extends RemoteObject{
	private $BTs;
	private $recipe;
	private $done;
	private $depth = 1;

	function __construct($dataArray){
		parent::__construct($dataArray);
		$this->BTs = array();
		$this->done =false;
	}
	public function test(){
		return $this->depth;
	}
	public function parse($results, $project){
		if(array_key_exists($sufix = $this->getTitle()."#".$project->getTitle(), $results)){
			//echo 'toto';
			$subobject = $results[$sufix]['printouts'];
			switch($subobject['Type'][0]){
				case 'RecetteInBT':
					$this->done = true;
					$this->extractRecipe($subobject['Recette liée'][0]);
					break;
				case 'BTInBT':
					$this->done = false;
					$this->extractTechReqs($results, $subobject['Besoin technique lié'], $project);
					break;
				default:
					break;
			}

		}
		return $this->depth;
	}
	public function extractRecipe($array){
		$this->recipe = new RemoteObject($array);
	}
	public function extractTechReqs($results, $array, $project){
		$depth = 0;
		foreach ($array as $techReqArray) {
			$bt = new BT($techReqArray);
			$depth = max($bt->parse($results, $project), $depth);
			$this->BTs[$bt->getTitle()] = $bt;
		}
		$this->depth = $depth+1;
	}
	public function BTGraphYourself($graph){
		switch ($this->done) {
			case true:
				$graph = $this->graphRecipe($graph);
				break;
			
			default:
				$graph = $this->graphBT($graph);
				break;
		}
		return $graph;
	}
	public function graphBT($graph){
		foreach ($this->BTs as $key => $value) {
			$urlTechReq= '';
			if($value != null){
				if($value->exists()){
					$urlTechReq= $value->getUrl();
				}
				$graph->addNode($key, array('URL' => $urlTechReq,  'shape' => 'box', 'color' => Color::colorNode('techreq')) );
				$graph->addEdge(array($this->getTitle() => $key), array('label' => "Se décompose en",'color' => Color::colorEdge('techreq')));
			}
		}
		return $graph;
	}
	public function graphRecipe($graph){
		$urlRecipe= '';
		if($this->recipe != null){
			$title = $this->recipe->getTitle();
			if($this->recipe->exists()){
				$urlRecipe= $this->recipe->getUrl();

			}
			$graph->addNode($title, array('URL' => $urlRecipe,  'shape' => 'box', 'color' => Color::colorNode('recipe')) );
			$graph->addEdge(array($this->getTitle() => $title), array('label' => "A comme recette",'color' => Color::colorEdge('recipe')));
		}
		return $graph;
	}
}