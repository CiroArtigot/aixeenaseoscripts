<?php

	/*------------------------------------------------------------------------
	# aixeenaseoscripts.php - Aixeena SEO Scripts (plugin)
	# ------------------------------------------------------------------------
	# version		1.0.0
	# author    	Ciro Artigot for Aixeena.org
	# copyright 	Copyright (c) 2018 CiroArtigot. All rights reserved.
	# @license 		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
	# Websites 		http://aixeena.org/
	-------------------------------------------------------------------------
	*/
	
	// no direct access
	defined('_JEXEC') or die('Restricted access');

	jimport('joomla.plugin.plugin');

	class plgSystemAixeenaSeoScripts extends JPlugin {

	
		function onBeforeCompileHead() {  
		
			$app	= JFactory::getApplication();
			if ($app->isAdmin()) return;
			$doc = JFactory::getDocument();
			
			$sw = 0;
			foreach($doc->_scripts as $t => $v) {
				plgSystemAixeenaSeoScripts::aix_addfoot('aixenaseo' . $sw, 'js', $t, $sw, '', null);
				unset($doc->_scripts[$t]);
				$sw++;
			}
	
			return true;
		}
		
	// ........................................................................................**** onAfterRender()
	function onAfterRender(){
			
		$app	= JFactory::getApplication();
		$document = JFactory::getDocument();
		if ($app->isAdmin()) return;
		if ($document->getType() != 'html') return;
		$document = JFactory::getDocument();
		$headerstuff = $document->getHeadData(); 
		$buffer = JResponse::getBody();

		preg_match_all('#<script(.*?)<\/script>#is', $buffer, $matches);
	
		$sw = 10000;
		foreach ($matches[0] as $value) {
			plgSystemAixeenaSeoScripts::aix_addfoot('aixenaseoscript' . $sw, 'code', '', $sw, $value, null);
			$buffer = str_replace($value, '', $buffer);	
			$sw++;
		}		
		
		$session = JFactory::getSession();
		$footcode = $session->get( 'aixeenaseo_footcode');
		$hayfoot = 0;
		$footcode_lines = '
';

		if(is_array($footcode) && count($footcode) > 0) {	

			$hayfoot = 1;
			foreach ($footcode as $key => $row) {
				$position[$key] = $row['position'];
			}
			array_multisort($position, SORT_ASC, $footcode);
				
			foreach($footcode as $v) {			
				$footcode_lines .= '	'.$v['code'].'
';			
			}	
		}
		if($hayfoot) $buffer =  str_replace('</body>', $footcode_lines.'	</body>',$buffer);			
		
		$newarray = array();
		$session->set('aixeenaseo_footcode', $newarray );
		
		JResponse::setBody($buffer);
		
		}
	
		
		public static function aix_addfoot($name, $type, $route, $position, $code, $option = null) {
			
			$session = JFactory::getSession();
			$footcode = $session->get( 'aixeenaseo_footcode');
			
			$url = $route;	
			if($type=='js') $code = '<script type="text/javascript" src="'.$url .'" '.$option.'></script>';	
			if($type=='script') $code = '<script type="text/javascript">'.$code.'</script>';

			
			$inscript = 1;		
			if(is_array($footcode)) {			
				foreach($footcode as $obj) {
					if(trim($obj['name']) == $name) {
						$inscript = 0;
						break;
					}
				}
			}
			
			if($inscript) $footcode[] =  array('name' => $name, 'url' => $url, 'position' => $position, 'type' => $type, 'code' => $code);
	
			$session->set('aixeenaseo_footcode', $footcode );
			return true;		
	
		}
	
	
	
		
	}
?>
