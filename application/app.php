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


abstract class Text2ImageApp
{
    
    public static function createImage($text, $fontsize, $fontcolor, $fontfile, $id, $i, $output)
    {
        
        // File does not exists
        ob_start ();
        
        // Calculate the Image Height and width
        $box = imagettfbbox($fontsize,0,$fontfile,$text);
        $width = abs($box[4] - $box[0]);
        $height = abs($box[5] - $box[1]);
        
        // Draw the image, need some space right otherwise it will cut off
        $im		= imagecreatetruecolor($width + 4, $height);
        
        // Colors
        $fontcolor	= substr($fontcolor, 1);
        $fontcolor	= str_split($fontcolor, 2);
        $textcolor1 = hexdec($fontcolor[0]);
        $textcolor2 = hexdec($fontcolor[1]);
        $textcolor3 = hexdec($fontcolor[2]);
        $textcolor	= imagecolorallocate($im, $textcolor1, $textcolor2, $textcolor3);
        
        // Make transparent
        imagesavealpha($im, true);
        $color = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $color);
        
        // Do the Image
        imagettftext($im, $fontsize, 0, 0, $fontsize, $textcolor, $fontfile,  $text);
        header("content-type: image/png");
        
        if($output == 0) {
            
            // base64 output
            imagepng($im);
            $image_data = ob_get_contents();
            $result		= base64_encode($image_data);
            
        } elseif($output == 1) {
            
            // PNG output
            $filename = 'images/text2image/tti'.$id.$i.'.png';
            jimport('joomla.filesystem.folder');
            // We need to create a folder for all the image files if the user decides to save them on the server
            $target = JPATH_SITE .'/images/text2image';
            $images_folder_exists = false;
            
            // Create
            if(!JFolder::exists($target))
            {
                // Success
                if(JFolder::create($target) == true)
                    $images_folder_exists = true;
                    
            } else {
                $images_folder_exists = true;
            }
            
            if($images_folder_exists == true) {
                imagepng($im, $filename);
                $result = $filename;
            } else {
                return false;
            }
        }
        
        ob_end_clean ();
        if($im) imagedestroy($im);
        
        return $result;
        
    }
}

?>