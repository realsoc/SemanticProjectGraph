<?php
class Color{
	private static $nodeColor = 
		array('techreq' => '#b2b2e0',
			'member' => '#ffff99',
			'project' => '#80cc99',
			'definition' => '#ffb2b2',
			'funcreq' => '#e6e6e6',
			'recipe' => '#ffb2ff',
			'ingredient' => '#ffcc80',
			'nonfuncreq' => '#ffcc80',
			'theme' => '#c299c2');	
	private static $edgeColor = 
		array('techreq' => '#00006b',
			'member' => '#999900',
			'project' => '#003d14',
			'definition' => '#cc0000',
			'funcreq' => '#434545',
			'recipe' => '#b200b2',
			'ingredient' => '#cc7a00',
			'nonfuncreq' => '#cc7a00',
			'theme' => '#470047');
	public static function colorNode($type){
		$nodeColor = self::$nodeColor;
		$color = $nodeColor[$type];
		return $color;
	}
	public static function colorEdge($type){
		$edgeColor = self::$edgeColor;
		$color = $edgeColor[$type];
		return $color;
	}
}