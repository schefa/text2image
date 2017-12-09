<?php
/**
 * @package     text2image
 * @author  	Fjodor Schaefer
 * @website		schefa.com
 *
 * @copyright   Copyright (C) 2014
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

require_once JPATH_SITE.'/plugins/content/text2image/application/app.php';

class PlgContentText2image extends JPlugin
{
	
	/**
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed   true if there is an error. Void otherwise.
	 *
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer')
		{
			return true;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, 'text2image') === false && strpos($article->text, 'text2image') === false)
		{
			return true;
		}
		
		// Expression to search for (positions)
		$matches 	= array();
		$regex		= '#\{text2image(.*)\}(.*)\{\/?text2image\}#siU';
		
		preg_match_all($regex,$article->text,$matches, PREG_SET_ORDER);
		
		// No matches, skip this
		if ($matches)
		{
			// Parameter
			$output		= $this->params->def('output', 0);
			$fontsize	= $this->params->def('fontsize', 13);
			$fontcolor	= $this->params->def('fontcolor', '#000000');
			$fontfile	= JPATH_SITE.'/plugins/content/text2image/application/fonts/'. $this->params->def('fontfile','opensans.ttf');;
			
			foreach ($matches as $i => $match)
			{
				// specific parameter
				if(!empty($match[1])) {
					// 1. Make an array where the parameters are store as value; key is their position
					$newParamsFields = explode(" ", trim($match[1]));
					// 2. Make an array  where the key is the title of the parameter, e.g. "fontsize" => 15
					foreach($newParamsFields AS $x => $field) {
						$new	= explode("=", $field);
						$newParams[ $new[0] ] = $new[1];
					}
					if(!empty($newParams['size'])) {
						$fontsize	= $newParams['size'];
					}
					if(!empty($newParams['color'])) {
						$fontcolor	= $newParams['color'];
					}
					if(!empty($newParams['font'])) {
						$fontfile	= JPATH_SITE.'/plugins/content/text2image/application/fonts/'. $newParams['font'] .'.ttf';
					}
				}
				
				// Line Breaks
				$text = preg_replace('/\<br(\s*)?\/?\>/i', "\n",$match[2]);
				$text = strip_tags($text);
				
				// create and returns the Image
				$image_data = Text2ImageApp::createImage($text, $fontsize, $fontcolor, $fontfile, $article->id, $i, $output);
				
				// output - base64 || png
				if($output == 0) {
					$result = '<img src="data:image/png;base64,'.$image_data.'" class="text2image" />';
				} elseif($output == 1) {
					$result = '<img src="'.$image_data.'" class="text2image" />';
				}
				
				// replace image with syntax
				$article->text = preg_replace("|$match[0]|", $result, $article->text );
			}
		}
		
	}
 
}
