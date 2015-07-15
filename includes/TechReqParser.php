<?php
require_once('RemoteObject.php');

class TechReqParser{
		require_once('Services/JSON.php');
		private $apiURL = "http://smw.learning-socle.org/api.php?";
		private $actionASK = "ask";
		private $queryPrefix = "&query=";
		private $actionPrefix = "&action=";
		private $formatPrefix = "&format=";
		private $formatJSON = "json";
		private $jsonService;
		function __construct(){
			$this->jsonService = new Services_JSON();
		}
		protected function jsonToObject($jsonString, $techReq){
			$results = json_decode($jsonString, true);
			if (count($results) > 0) {
				$results = $results["query"]["results"];
				$jsonTechReq = $results[$techReq->getTitle()]["printouts"];
				$this->extractRecipes($techReq, $jsonTechReq);
				$this->extractIngredients($techReq, $jsonTechReq);
				$this->extractDefinitions($techReq, $jsonTechReq);
				$this->extractTheme($techReq, $jsonTechReq);
			}

			return $techReq;
		}
		public function extractTheme($project, $jsonTechReq){
			if($jsonTechReq["A thème"] != null && $jsonTechReq["A thème"][0]!= null){
				$theme = $jsonTechReq["A thème"][0];
				$project->setTheme($theme);			
			}

		}
		public function extractRecipes($techReq, $jsonTechReq){
			if($jsonTechReq["Recette liée"] != null){
				foreach ($jsonTechReq["Recette liée"] as $el) {
					$recipe = new RemoteObject($el);
					$techReq->addRecipe($recipe);
				}		
			}
			
		}
		public function extractIngredients($techReq, $jsonTechReq){
			if($jsonTechReq["Ingrédient lié"] != null){
				foreach ($jsonTechReq["Ingrédient lié"] as $el) {
					$ingredient = new RemoteObject($el);
					$techReq->addIngredient($ingredient);
				}	
			}

		}
		public function extractDefinitions($techReq, $jsonTechReq){
			if($jsonTechReq["Définition liée"] != null){
				foreach ($jsonTechReq["Définition liée"] as $el) {
					$definition = new RemoteObject($el);
					$techReq->addDefinition($definition);
				}
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