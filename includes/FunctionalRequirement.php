<?php

class FunctionalRequirement{
	private $title;
	private $sons;
	private $isLast;
	private $recette;

	function __construct($title, $recette = null){
		$this->title = $title;
		$this->sons = array();
		if($recette != null){
			$this->isLast = true;
			$this->recette = $recette;
		}
	}
	public function getSons(){
		return $this->sons;
	}
	public function getRecette(){
		return $this->recette;
	}
	public function getLast(){
		return $this->isLast;
	}
	public function isLast(){
		$this->isLast = true;
	}
	public function setTitle($title){
		$this->title = $title;
	}
	public function getTitle(){
		return $this->title;
	}
	/*public function funcReqByTitle($title){
		$ret = null;
		if($this->hasfuncReqByTitle($title)){
			$ret = $this->funcReqs[$title];
		}
		return $ret;
	}*/
	private function cleanTree($funcsReqString){
		$count = 1;
		$depth = 0;
		while($count != 0){
			$depth++;
			$strToReplace = "/<[-]{".$depth."}>/";
			$by = "<".$depth.">";
			$funcsReqString = preg_replace($strToReplace, $by,$funcsReqString, -1, $count);
		}

		return $funcsReqString;
	}
	public function addSon($son){
		array_push($this->sons, $son);
	}
	//<1>BF1 <2>SBF1 <3>SSBF1 +{Recette10 <3>SSBF2 +Recette11 <2>SBF2 +Recette2 <1>BF2 +Recette3toto
	public function sonsExtractor($treeString){
		$treeString = $this->cleanTree($treeString);
		$pattern = "/<1>/";
		$tree = preg_split($pattern, $treeString, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($tree as $key => $value){
			$this->addSon($this->extractSon($this->deeper($value)));

		}/*
		if($this->getLast()){
			//echo 'last <br>';
		}
		if($this->getRecette() != null){
			//echo '<br> recette : '.$this->getRecette.'<br>';
		}*/
	}
	public function showRecur(){
		$toto = "<br> Me: ".$this->title;
		if($this->recette !=null){
			$toto .= 'je suis dernier et ma recette est : '.$this->recette.'<br>';
		}
		else if($this->isLast){
			$toto.=" je suis le dernier mais je n'ai pas de recettes <br>";
		}
		else{
			$i = 0;
			$toto .= "j'ai des enfants : ";
			foreach ($this->sons as $value) {
				$i++;
				$toto.= 'enfant '.$i.$value->showRecur().'<br>';
			}
		}
		return $toto;

	}
	public function extractSon($newString){
		$newSon = null;
		$arrayResult = preg_split("/<1>/", $newString);
		$name = $arrayResult[0];
		if(strpos($name, '+') !== FALSE){
			$funcWRecipeTab = split('\+', $name);
			$name = $funcWRecipeTab[0];
			$recipe = $funcWRecipeTab[1];
			$newSon = new FunctionalRequirement($name, $recipe);
		}else if(sizeof($arrayResult) == 1){
			$newSon = new FunctionalRequirement($name);
			$newSon->isLast();
		}else{
			$newStr ="";
			for($i=1;$i<sizeof($arrayResult);$i++){
				$newStr .= '<1>'.$arrayResult[$i];
			}
			$newSon = new FunctionalRequirement($name);
			$newSon->sonsExtractor($newStr);
		}
		return $newSon;
	}
	//purpose is to reduce the depth of the string representation of the new tree by 
	//<2>SBF1 <3>SSBF1 =>  <1>SBF1 <2>SSBF1
	public function deeper($treeString){
		$depth = 1;
		$count = 1;
		while($count != 0){
			$depth++;
			$strToReplace = "/<".$depth.">/";
			$temp = $depth-1;
			$by = "<".$temp.">";
			$treeString = preg_replace($strToReplace, $by,$treeString, -1, $count);
		}
		return $treeString;
	}
	public function FRGraphYourself($graph){
		if($this->isLast){
			if($this->recette != null){
				$graph->addNode($this->recette, array('URL' => 'http://smw.learning-socle.org/index.php/'.$this->recette,'shape' => 'box', 'color' => Color::colorNode('recipe')) );
				$graph->addEdge(array($this->title => $this->recette), array('label' => "A comme recette",'color' => Color::colorEdge('recipe')));
			}
		}else{
			foreach ($this->sons as $value) {
				$graph->addNode($value->getTitle(), array('shape' => 'box', 'color' => Color::colorNode('funcreq')) );
				$graph->addEdge(array($this->title => $value->getTitle()), array('label' => "Se décompose en",'color' => Color::colorEdge('funcreq')));
				$graph = $value->FRGraphYourself($graph);
			}
		}
		return $graph;
	}

}

