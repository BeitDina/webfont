<?php
/**
 * Copyright © 2015, Ambroise Maupate
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
 * @file WoffConverter.php
 * @author Ambroise Maupate
 */
namespace WebfontGenerator\Converters;

use WebfontGenerator\Util\StringHandler;
use WebfontGenerator\Converters\Driver;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class WoffConverter
 *
 * @package WebfontGenerator\Converters
 */
class WoffConverter implements ConverterInterface
{
    protected $woffCompress = null;

    public function __construct($binPath)
    {
		$this->driver = new Driver();
		if ('\\' === \DIRECTORY_SEPARATOR) 
		{	
			//If we use usr/bin for sfnt2woff on Windows
			if ($this->driver->file_exists($binPath))
			{
				$this->woffCompress = $binPath;
			}
			else
			{
				//Rename config-default-win.yml to config.yml
				$this->woffCompress = $binPath . '.exe';
			}		
		}
		else
		{
			//If we use usr/bin for sfnt2woff on Linux
			$this->woffCompress = $binPath;
		}
		
    }
	
    public function convert(File $input)
    {
        if (!$this->driver->file_exists($this->woffCompress)) 
		{
			throw new \RuntimeException("could not be found (sfnt2woff): ".$this->woffCompress);
        }
		
        $output = array([]);
		$outFile = str_replace(array('.TTF.woff', '.ttf.woff', '.OTF.woff', '.otf.woff'), '.woff', $this->getWOFFPath($input));
		$inpFile = $input->getRealPath();
		$inpFileExt = substr(strrchr($inpFile, '.'), 1);
		$outFileExt = substr(strrchr($outFile, '.'), 1);
		$compressor = $this->woffCompress;
		$return = 0;
		
		//MB_CASE_LOWER, MB_CASE_UPPER, MB_CASE_TITLE
		if (ucwords($inpFileExt) === $inpFileExt)
		{
			$outFile = str_replace(array('.woff', '.woff2'), '.TTF.woff', $this->getWOFFPath($input));
			print '(caps upper case extension) The converter made append to file extension for sfnt2woff -o ' . $outFile . ' and is OK.';
        }
		
		//If is not a compatible font
		if (str_replace(array('.SVG', '.svg'), '.ttf', $inpFile) !== $inpFile)
		{			
			$inpFile = str_replace(array('.SVG', '.svg'), '.ttf', $inpFile);			
			//$compressor = str_replace('sfnt2woff', 'svg2woff', $input->woffCompress);
			print '(svg to woff) The converter detected other mime file type and/or extension incompatible with sfnt2woff ' . $inpFile . ' and is OK.';
			exec($compressor . ' "'. $inpFile .'"', $output, $return);
		}
		elseif (str_replace(array('.WOFF2', '.woff2'), '.ttf', $inpFile) !== $inpFile)
		{			
			$inpFile = str_replace(array('.WOFF2', '.woff2'), '.ttf', $inpFile);			
			//$compressor = str_replace('sfnt2woff', 'svg2woff', $input->woffCompress);
			print '(woff to woff) The converter detected other mime file type and/or extension incompatible with sfnt2woff ' . $inpFile . ' and is OK.';
			exec($compressor . ' "'. $inpFile .'"', $output, $return);
		}
		elseif (str_replace(array('.WOFF', '.woff'), '.ttf', $inpFile) !== $inpFile)
		{			
			$inpFile = str_replace(array('.WOFF', '.woff'), '.ttf', $inpFile);			
			//$compressor = str_replace('sfnt2woff', 'svg2woff', $input->woffCompress);
			print '(woff to woff) The converter detected other mime file type and/or extension incompatible with sfnt2woff ' . $inpFile . ' and is OK.';
			exec($compressor . ' "'. $inpFile .'"', $output, $return);
		}
		elseif (str_replace(array('.EOT', '.eot'), '.ttf', $inpFile) !== $inpFile)
		{			
			$inpFile = str_replace(array('.EOT', '.eot'), '.ttf', $inpFile);			
			//$compressor = str_replace('sfnt2woff', 'svg2woff', $input->woffCompress);
			print '(eot to woff) The converter detected other mime file type and/or extension incompatible with sfnt2woff ' . $inpFile . ' and is OK.';
			exec($compressor . ' "'. $inpFile .'"', $output, $return);
		}		
		else
		{
			exec($compressor . ' "'. $inpFile .'"', $output, $return);
		}
		//die(print_r(array($compressor . ' "'. $inpFile .'"', $output, $return), true));		
				
        if (0 !== $return) 
		{
            throw new \RuntimeException('CMD Line: ' . $compressor  . ' "'. $inpFile .'" '. 'could not convert '. $inpFileExt . ' file ' . $input->getBasename().' to Woff format.');
        } 
		else 
		{
            return new File($outFile);
        }
    }
	
	public function getWOFFPath(File $input)
    {
        $basename = StringHandler::slugify($input->getBasename('.'.$input->getExtension()));

        return $input->getPath().DIRECTORY_SEPARATOR.$basename.'.woff';
    }
}
