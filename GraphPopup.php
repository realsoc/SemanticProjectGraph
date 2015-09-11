<?php
require_once 'includes/Project.php';
require_once 'includes/Recipe.php';
if(isset($_GET['project']) && !isset($_GET['recipe']) ){
    $project = new Project($_GET['project']);	
    $project->retrieveData();
	if($project->isFound()){	
    	$project->showGraph();
    }else{
    	echo "Object not found";
    }
}elseif(isset($_GET['recipe']) && !isset($_GET['project']) ){
    $recipe = new Recipe($_GET['recipe']);
    $recipe->retrieveData();
	if($recipe->isFound()){	
    	$recipe->showGraph();
    }else{
    	echo "Object not found";
    }
}