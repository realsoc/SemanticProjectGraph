<?php 
require_once('RemoteObject.php');
require_once('RemoteRecipe.php');
//require_once 'BT.php';
require_once 'Login.php';
require_once 'FunctionalRequirement.php';

class ProjectParser {
		private $apiURL = "/api.php?";
		private $actionASK = "ask";
		private $queryPrefix = "&query=";
		private $actionPrefix = "&action=";
		private $formatPrefix = "&format=";
		private $formatJSON = "json";
		private $jsonService;
		private $mn;

		function __construct(){
			$this->mn = new Login();
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
				}
			}
			return $project;
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
			$strTree = $jsonProject["ListeBF"][0];
			$project->setFuncReqs($this->parseFuncsReq($strTree)->getSons());
			//$project->setFuncsReqTree($jsonProject["ListeBF"]);
		}
		public function retrieveInfoForObject($object){
			$jsonString = $this->getObjectAsJson($object);
			$this->jsonToObject($jsonString, $object);
		}
		private function getObjectAsJson($object){
			$mQuery=urlencode($object->getQuery());
			$url=$this->apiURL.$this->actionPrefix.$this->actionASK.$this->queryPrefix.$mQuery.$this->formatPrefix.$this->formatJSON;
			return $this->mn->callApi($url);
			//return $this->mn->callApi($url);
		}
		//<->BF1 <-->SBF1 <--->SSBF1 +Recette10 <--->SSBF2 +Recette11 <-->SBF2 +Recette2 <->BF2 +Recette3
		private function parseFuncsReq($funcsReqString){
			$root = new FunctionalRequirement('root');
			$root->sonsExtractor($funcsReqString);
			//echo $root->showRecur();
			return $root;
		}
	}
?>

