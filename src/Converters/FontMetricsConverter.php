<?php
/**
 * Copyright © 2021, Florin C Bodin
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file FontMetricsConverter.php
 * @author Florin C Bodin
 */
namespace WebfontGenerator\Converters;

use WebfontGenerator\Util\StringHandler;
use WebfontGenerator\Converters\Driver;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class FontMetricsConverter
 *
 * @package WebfontGenerator\Converters
 */
class FontMetricsConverter implements ConverterInterface
{
    protected $fontforge = null;

    public function __construct($binPath)
    {
		$this->driver = new Driver();
		if (('\\' === \DIRECTORY_SEPARATOR) && (PHP_OS !== 'Linux')) 
		{	
			//If we use usr/bin for fontforge on Windows
			if ($this->driver->file_exists($binPath))
			{
				$this->fontforge = $binPath;
			}
			else
			{
				//Rename config-default-win.yml to config.yml
				$this->fontforge = $binPath . '.exe';
			}		
		}
		else
		{
			//If we use usr/bin for fontforge on Linux
			$this->fontforge = $binPath;
		}
    }

    public function convert(File $input)
    {
	
		if (!$this->driver->file_exists($this->fontforge))
		{
			throw new \RuntimeException("could not be found: ".$this->fontforge);
        }
              
        $output = array([]);
        $outFile = $this->getSFDPath($input);
		$inpFile = $input->getRealPath();
		$inpFileExt = substr(strrchr($inpFile, '.'), 1);
		
		$return = 0;
		exec($this->fontforge . ' -script '.ROOT.'/assets/scripts/toamf.pe "'.$inpFile.'"', $output, $return);
		
		if (0 !== $return) 
		{		
			$return = 0;
			if (!$this->driver->file_exists($this->fontforge)) 
			{
				throw new \RuntimeException('Fontforge could not convert '.$input->getBasename().' to TrueType format on ' . PHP_OS . 
				'. Try: ' . $this->fontforge . ' ' . ' "'.$inpFile.'" N(0-8)');
			}				
				
			$inpFile = str_replace(array('.TTF', '.ttf'), '.ttf', $inpFile);
			//fontforge -lang=ff -c 'Open($1); Generate($2)' "$1" "$2"
			exec($this->fontforge . " -lang="."ff"." -c 'Open(".$inpFile."); Generate(".$outFile.")'", $output, $return);
				
			if (0 !== $return) 
			{	
				print('Try: '. $this->fontforge . " -lang="."ff"." -c 'Open(".$inpFile."); Generate(".$outFile.")'");
			}
				
			return new File($outFile);
        }
		else 
		{
            return new File($outFile);
        }
		
		/*
		exec($this->fontforge . ' -script '.ROOT.'/assets/scripts/tottf.pe "'.$outFile.'"', $output, $return);
		if (0 !== $return) 
		{
			throw new \RuntimeException('Fontforge could not convert '.$input->getBasename().' to TrueType format on ' . PHP_OS . 
			'. Try: ' . $this->fontforge . ' -script '.ROOT.'/assets/scripts/tottf.pe "'.$inpFile.'"');
		} 
		else 
		{
			return new File($outFile);
		}
		*/
    }

    public function getSFDPath(File $input)
    {
        $basename = StringHandler::slugify($input->getBasename('.'.$input->getExtension()));

        return $input->getPath().DIRECTORY_SEPARATOR.$basename.'.afm';
    }
}
