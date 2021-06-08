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
 * @file SplitFontDBConverter.php
 * @author Florin C Bodin
 */
namespace WebfontGenerator\Converters;

use WebfontGenerator\Util\StringHandler;
use WebfontGenerator\Converters\Driver;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class SlitFontDBConverter
 *
 * @package WebfontGenerator\Converters
 */
class SplitFontDBConverter implements ConverterInterface
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
		
		//MB_CASE_LOWER, MB_CASE_UPPER, MB_CASE_TITLE
		if (ucwords($inpFileExt) === $inpFileExt)
		{
			$fntFileEx = 'TTF';
        }
		else		
		{
			$fntFileEx = 'ttf';
        }	
		
		$return = 0;
		exec($this->fontforge . ' -script '.ROOT.'/assets/scripts/tosfd.pe "'.$inpFile.'"', $output, $return);
		
		if (0 !== $return) 
		{		
			$return = 0;
			if ((str_replace(array('.WOFF2', '.woff2'), '.woff2', $inpFile) == $inpFile) || (str_replace(array('.WOFF2', '.woff2'), '.WOFF2', $inpFile) == $inpFile))
			{			
				//woff2 decompress not supported by fontforge
				print '(woff2 decompress) The converter detected other mime file type and/or extension incompatible with fontforge for ' . basename($inpFile) . ' and is OK.';
				exec(str_replace('fontforge', 'woff2_decompress', $this->fontforge) . ' "'. $inpFile .'"', $output, $return);
				
				if (0 !== $return) 
				{	
					print('Font compression exception (' . $this->driver->file_exists($outFile). '): The converter changed the ' . basename($inpFile) . ' font compression.');
				}		
			}			
			elseif (!$this->driver->file_exists($this->fontforge)) 
			{
				throw new \RuntimeException('Fontforge could not convert '.$input->getBasename().' to TrueType format on ' . PHP_OS . 
				'. Try: ' . $this->fontforge . ' ' . ' "'.$inpFile.'" N(0-8)');
			}				
				
			$inpFile = str_replace(array('.WOFF2', '.woff2', '.OTF', '.otf', '.TTF', '.ttf'), '.' . $fntFileEx, $inpFile);
			
			//fontforge -lang=ff -c 'Open($1); Generate($2)' "$1" "$2"
			//exec($this->fontforge . " -lang="."ff"." -c 'Open(".$inpFile."); Generate(".$outFile.")'", $output, $return);
			exec($this->fontforge . ' -script '.ROOT.'/assets/scripts/tosfd.pe "'.$inpFile.'"', $output, $return);
			
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

        return $input->getPath().DIRECTORY_SEPARATOR.$basename.'.sfd';
    }
}
