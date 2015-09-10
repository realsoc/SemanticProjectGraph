<?php
header("Content-type: text/html; charset=UTF-8");
require_once('RemoteObject.php');
require_once 'Services/JSON.php';

class RecipeParser{
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
		protected function jsonToObject($jsonString, $recipe){
			$results = json_decode($jsonString, true);
			if (count($results) > 0) {
				$results = $results["query"]["results"];
				$title = $recipe->getTitle();
				$jsonRecipe = $results["$title"]["printouts"];
				$this->extractMembers($recipe, $jsonRecipe);
				$this->extractIngredients($recipe, $jsonRecipe);
				$this->extractDefinitions($recipe, $jsonRecipe);
				$this->extractTechReqs($recipe, $jsonRecipe);
				$this->extractProjects($recipe, $jsonRecipe);

				$this->extractTheme($recipe, $jsonRecipe);
				$this->extractFather($recipe, $jsonRecipe);
			}
			return $recipe;
		}
		public function extractFather($recipe, $jsonRecipe){
			if(array_key_exists(0, $jsonRecipe["Découle du besoin technique"])){
				$father = new RemoteObject($jsonRecipe["Découle du besoin technique"][0]);
				$recipe->setFather($father);
			}
		}
		public function extractTheme($recipe, $jsonRecipe){
			$theme = $jsonRecipe["A thème"][0];
			$recipe->setTheme($theme);
		}

		public function extractMembers($recipe, $jsonRecipe){
			foreach ($jsonRecipe["A membre"] as $el) {
					$member = new RemoteObject($el);
					$recipe->addMember($member);
			}
		}
		public function extractIngredients($recipe, $jsonRecipe){
			foreach ($jsonRecipe["Ingrédient lié"] as $el) {
				$ingredient = new RemoteObject($el);
				$recipe->addIngredient($ingredient);
			}
		}
		public function extractDefinitions($recipe, $jsonRecipe){
			foreach ($jsonRecipe["Définition liée"] as $el) {
				$definition = new RemoteObject($el);
				$recipe->addDefinition($definition);
			}
		}
		public function extractTechReqs($recipe, $jsonRecipe){
			foreach($jsonRecipe["Besoin technique lié"] as $el){
				$techReq = new RemoteObject($el);
				$recipe->addTechReq($techReq);
			}
		}
		public function extractProjects($recipe, $jsonRecipe){
			foreach($jsonRecipe["Projet lié"] as $el){
				$project = new RemoteObject($el);
				$recipe->addProject($project);
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