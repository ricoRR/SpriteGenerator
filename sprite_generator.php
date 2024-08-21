<?php

foreach ($argv as $value) {
    
    if(is_dir($value) == true) {
        $path = realpath($value);
    }
}

$width = 0;
$height = 0;
$switch = false;
$e = 0;
$padding = 0;
$name = "sprite.png";
$css_name = "style.css";

if(array_search("-r", $argv) == true or array_search("--recursive", $argv) == true) {
    $recursive = true;
} else {
    $recursive = false;
}
    
if(array_search("-i", $argv) == true) {
        $next = array_search("-i", $argv);
        $next = $next + 1;
        $name = $argv[$next];
    }

if(array_search("--output-style", $argv) == true) {
        $next = array_search("--output-style", $argv);
        $next = $next + 1;
        $name = $argv[$next];
    }

if(array_search("-p", $argv) == true) {
        $next = array_search("-p", $argv);
        $next = $next + 1;
        $padding = $argv[$next];
    }

if(array_search("--padding", $argv) == true) {
        $next = array_search("--padding", $argv);
        $next = $next + 1;
        $padding = $argv[$next];
    }

if(array_search("-s", $argv) == true) {
        $next = array_search("-s", $argv);
        $next = $next + 1;
        $css_name = $argv[$next];
    }

if(array_search("--output-image", $argv) == true) {
        $next = array_search("--output-image", $argv);
        $next = $next + 1;
        $css_name = $argv[$next];
    }

if(array_search("-c", $argv) == true) {
        $next = array_search("-c", $argv);
        $next = $next + 1;
        $e = $argv[$next];
        $switch = true;
    }

if(array_search("--columns_number", $argv) == true) {
        $next = array_search("--columns_number", $argv);
        $next = $next + 1;
        $e = $argv[$next];
        $switch = true;
    }
    
function find_dir($path, &$png_ar, $recursive, &$png_name) {
    
    $dir_handle = opendir($path);
    
    while (($file = readdir($dir_handle)) == true){       
        
        if ($file !== '.' and $file !== '..') {           
            if(is_file("$path/$file") and exif_imagetype("$path/$file") == IMAGETYPE_PNG) {               
                $png_ar[] ="$path/$file";
                $png_name[] = $file;
            }
            else if(is_dir("$path/$file") and $recursive) {
                find_dir("$path/$file", $png_ar, $recursive, $png_name);
            }
        }
    }

    closedir($dir_handle);
} 
$png_ar = array();
$png_name = array();
find_dir($path,$png_ar,$recursive, $png_name);

function generate_sprite_sheet($name, $png_ar, $width, $height, $e, $padding, $switch, $css_name, $png_name) {
    
    $m = 0;
    $old_w = 0;
    $old_h = 0;
    $max_h = 0;
    $max_w = 0;

    foreach($png_ar as $value) {
        
        if(exif_imagetype($value) == IMAGETYPE_PNG and $e > 0) {
            $ar = getimagesize($value);
            list($w, $h) = $ar;
            $old_w = $w;
            $old_h = $h;
            $ar_nbr = count($png_ar);
            $h_mult = $ar_nbr / $e;
            $h_mult = ceil($h_mult);
            $e2 = $old_w * ($e - 1);
            $e3 = ($max_h * ($h_mult) - $old_h);

            if($width < $w + $e2) {
                $width = $w + $e2;
            }

            if($max_w < $w) {
                $max_w = $w;
            }
            
            if($height < $h + $e3) {
                $height = $h + $e3;
            }

            if($max_h < $h) {
                $max_h = $h;
            }
             
        }
        else if(exif_imagetype($value) == IMAGETYPE_PNG and $e == 0) {
                        $ar = getimagesize($value);
        list($w, $h) = $ar;
        $width = $width + $w + $padding;
    
        if($h>$height) {
            $height = $h;
        }
        }
        $old_w = $w;
        $old_h = $h;
    }

    if($width > 0 and $height > 0) {
        $background = imagecreatetruecolor($width, $height);
        $color = imagecolorallocatealpha($background, 0, 0, 0, 127 );
        imagefill($background, 0, 0, $color);
        imagesavealpha($background, true);
    }
    
    $d = 0;
    $f = 0;
    $k = -1;

    foreach($png_ar as $value) { 
        
        if($e > 0 and $switch == true) {
            $k++;
        }
        
        if($k == $e and $switch == true) {
            $f = $max_h + $f;
            $d = 0;
            $k = 0;
        }
        
        if(exif_imagetype($value) == IMAGETYPE_PNG) {
            $ar = getimagesize($value);
            list($w, $h) = $ar;
            $sprite = imagecreatefrompng($value);
            imagecopy($background, $sprite, $d, $f, 0, 0, $w, $h);
            $d = $d + $w + $padding;
        }
        
    }
    
    if($width > 0 and $height > 0){  
        $css_file = fopen($css_name, "w+");
        
        foreach($png_name as $value) {
            
            if(str_contains($value, ".png")) {
                $value = substr($value, 0, -4);
            }
            fwrite($css_file, ".$value { background-position: -{$d}px -{$f}px; width: {$w}px; height: {$h}px; }" . PHP_EOL);
        }
    }
        
    if($width > 0 and $height > 0){  
        imagepng($background, $name);
    }else {
        exit("Error insufficient data to proceed" . PHP_EOL);
    }
}

generate_sprite_sheet($name, $png_ar, $width, $height, $e, $padding, $switch, $css_name, $png_name);