<?php

class FunctionalRequirement{
	private $title;
	private $techReqs;
	function __construct($title){
		$this->title = $title;
		$this->techReqs = array();
	}
	public function setTitle($title){
		$this->title = $title;
	}
	public function addTechReq($techReq){
		$this->techReqs[$techReq->getTitle()] = $techReq;
	}
	public function getTitle(){
		return $this->title;
	}
	public function techReqByTitle($title){
		$ret = null;
		if($this->hasTechReqByTitle($title)){
			$ret = $this->techReqs[$title];
		}
		return $ret;
	}
	public function hasTechReqByTitle($title){
		return array_key_exists($title, $this->techReqs);
	}
	//A REVOIR
	public function FRGraphYourself($graph){
		foreach($this->techReqs as $key => $value){
			$urlTechReq= '';
			if($value != null){
				if($value->exists()){
					$urlTechReq= $value->getUrl();
				}else{
					$urlTechReq = $value->getUrl();
				echo $url."AVANT";
				}
				$graph->addNode($key, array('URL' => $urlTechReq,  'shape' => 'box', 'color' => Color::colorNode('techreq')) );
				$graph->addEdge(array($this->title => $key), array('label' => "A comme besoin technique",'color' => Color::colorEdge('techreq')));
				$graph = $value->BTGraphYourself($graph);
			}
		}
		return $graph;
	}

}

