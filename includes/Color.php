<?php
class Color{
	private static $nodeColor = 
		array('techreq' => '#B2B2E0',
			'member' => '#FFFF99',
			'project' => '#80CC99',
			'definition' => '#FFB2B2',
			'funcreq' => '#E6E6E6',
			'recipe' => '#FFB2FF',
			'ingredient' => '#FFCC80',
			'theme' => '#C299C2');	
	private static $edgeColor = 
		array('techreq' => '#00006B',
			'member' => '#999900',
			'project' => '#003D14',
			'definition' => '#CC0000',
			'funcreq' => '#434545',
			'recipe' => '#B200B2',
			'ingredient' => '#CC7A00',
			'theme' => '#470047');
	public static function colorNode($type){
		$nodeColor = self::$nodeColor;
		return array('color'=>$nodeColor[$type]);
	}
	public static function colorEdge($type){
		$edgeColor = self::$edgeColor;
		return array('color'=>$edgeColor[$type]);
	}
}