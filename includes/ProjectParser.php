<?php 
require_once('RemoteObject.php');
require_once('RemoteRecipe.php');
require_once 'BT.php';

class ProjectParser {
		private $apiURL = "http://smw.learning-socle.org/api.php?";
		private $actionASK = "ask";
		private $queryPrefix = "&query=";
		private $actionPrefix = "&action=";
		private $formatPrefix = "&format=";
		private $formatJSON = "json";
		private $jsonService;

		function __construct(){
		}
		protected function jsonToObject($jsonString, $project){
			$results = json_decode($jsonString, true);
			if (count($results) > 0) {
				if(array_key_exists("results", $results["query"]) && count($results["query"]["results"]) >0){
					$project->setFound(true);
					$results = $results["query"]["results"];
					$jsonProject = $results[$project->getTitle()]["printouts"];
					$this->extractMembers($project, $jsonProject);
					$this->extractIngredients($project, $jsonProject);
					$this->extractDefinitions($project, $jsonProject);
					$this->extractNonFunReqs($project, $jsonProject);
					$this->extractFuncReqs($project, $jsonProject);
					foreach ($project->getFuncReqs() as $el) {
						$title = $el->getTitle();
						$this->extractTechReq($project, $results, $title);
					}
				}
			}
			return $project;
		}
		public function extractTechReq($project, $results, $funcReqName){
			foreach($results[$project->getTitle()."#".$funcReqName]["printouts"]["Contenu"] as $techReqArray){
				$techReq = new BT($techReqArray);
				$techReq->parse($results, $project);
				$project->addTechToFunc($techReq, $funcReqName);
			}
		}

		public function extractMembers($project, $jsonProject){
			foreach ($jsonProject["A membre"] as $el) {
				$member = new RemoteObject($el);
				$project->addMember($member);
			}
		}
		public function extractNonFunReqs($project, $jsonProject){
			foreach ($jsonProject["Besoin non fonctionnel lié"] as $el) {
				$nFuncReq = new RemoteObject($el);
				//var_dump($el);
				$project->addNonFuncReq($nFuncReq);
			}
		}
		public function extractIngredients($project, $jsonProject){
			foreach ($jsonProject["Ingrédient lié"] as $el) {
				$ingredient = new RemoteObject($el);
				$project->addIngredient($ingredient);
			}
		}
		public function extractDefinitions($project, $jsonProject){
			foreach ($jsonProject["Définition liée"] as $el) {
				$definition = new RemoteObject($el);
				$project->addDefinition($definition);
			}
		}
		public function extractFuncReqs($project, $jsonProject){
			foreach($jsonProject["Besoin fonctionnel lié"] as $el){
				$project->addFuncReq($el);
			}
		}
		public function retrieveInfoForObject($object){
			$jsonString = $this->getObjectAsJson($object);
			$this->jsonToObject($jsonString, $object);
		}
		private function getObjectAsJson($object){
			$mQuery=urlencode($object->getQuery());
			$url=$this->apiURL.$this->actionPrefix.$this->actionASK.$this->queryPrefix.$mQuery.$this->formatPrefix.$this->formatJSON;
			return file_get_contents($url);
		}
	}
?>