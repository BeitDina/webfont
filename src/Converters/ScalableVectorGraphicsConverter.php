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
 * @file ScalableVectorGraphicsConverter.php
 * @author Ambroise Maupate
 */
namespace WebfontGenerator\Converters;

use WebfontGenerator\Util\StringHandler;
use WebfontGenerator\Converters\Driver;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ScalableVectorGraphicsConverter
 *
 * @package WebfontGenerator\Converters
 */
class ScalableVectorGraphicsConverter implements ConverterInterface
{
    protected $fontforge = null;

    public function __construct($binPath)
    {
		$this->driver = new Driver();
		if (('\\' === \DIRECTORY_SEPARATOR) && (PHP_OS !== 'Linux'))
		{	
			//If we use usr/bin for sfnt2woff on Windows
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
			//If we use usr/bin for sfnt2woff on Linux
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
		$inpFile = $input->getRealPath();
		$inpFileExt = substr(strrchr($inpFile, '.'), 1);
		$return = 0;
		
		//MB_CASE_LOWER, MB_CASE_UPPER, MB_CASE_TITLE
		if (ucwords($inpFileExt) === $inpFileExt)
		{
			$fntFileEx = 'TTF';
        }
		else		
		{
			$fntFileEx = 'ttf';
        }
		
        if (!$this->driver->file_exists($outFile = $this->getSVGPath($input))) 
		{
			$outFile = str_replace(array('.woff', '.OTF.woff'), '.TTF.woff', $this->getSVGPath($input));
			//print 'ATM exists not -o ' . $outFile . ' OK';
        }
        
		if (!$this->driver->file_exists($inpFile = $input->getRealPath())) 
		{
			$inpFile = str_replace(array('.woff', '.OTF.woff'), '.TTF.woff', $input->getRealPath());
			//$inpFile = str_replace(array('_', '-'), '-', $inpFile);
			//print 'ATM exists not -i ' . $inpFile . ' ?';			
        } 
		//print '/usr/bin/fontforge.exe -script C:\Wamp\www\webfont/assets/scripts/tosvg.pe ' . $inpFile . ' ';
 		//If is not a compatible font
		if (str_replace(array('.SVG', '.svg'), '.ttf', $inpFile) !== $inpFile)
		{			
			$inpFile = str_replace(array('.SVG', '.svg'), '.ttf', $inpFile);
			//$outFile = str_replace(array('.svg', '.SVG'), '.TTF.svg', $this->getSVGPath($input));			
			print '(svg) The converter detected other mime file type and/or extension ' . $inpFileExt . ' compatible with fontforge -script /assets/scripts/tosvg.pe ' . $inpFile . ' and is OK.';
			exec($this->fontforge . ' -script '. str_replace('/\/', '/', ROOT) .'/assets/scripts/tosvg.pe "' . $inpFile . '"',
				$output,
				$return
			);
		}
		elseif (str_replace(array('.WOFF2', '.woff2'), '.ttf', $inpFile) !== $inpFile)
		{			
			$inpFile = str_replace(array('.WOFF2', '.woff2'), '.' . $fntFileEx, $inpFile);
			//$compressor = str_replace('sfnt2woff', 'svg2woff', $input->woffCompress);
			//Windows Branch ported from https://github.com/BeitDina/webfont-generator/tree/windows
			print '(woff2) The converter detected other mime file type and/or extension incompatible with fontforge ' . $inpFile . ' and is OK.';
			exec($this->fontforge . ' -script '. str_replace('/\/', '/', ROOT) .'/assets/scripts/tosvg.pe "' . $inpFile . '"',
				$output,
				$return
			);
		}	
		elseif (str_replace(array('.WOFF', '.woff'), '.ttf', $inpFile) !== $inpFile)
		{			
			$inpFile = str_replace(array('.WOFF', '.woff'), '.ttf', $inpFile);			
			//$compressor = str_replace('sfnt2woff', 'svg2woff', $input->woffCompress);
			print '(woff) The converter detected other mime file type and/or extension compatible with fontforge ' . $inpFile . ' and is OK.';
			exec($this->fontforge . ' -script '. str_replace('/\/', '/', ROOT) .'/assets/scripts/tosvg.pe "' . $inpFile . '"',
				$output,
				$return
			);	
		}
		elseif (str_replace(array('.EOT', '.eot'), '.ttf', $inpFile) !== $inpFile)
		{			
			$inpFile = str_replace(array('.EOT', '.eot'), '.ttf', $inpFile);			
			//$compressor = str_replace('sfnt2woff', 'svg2woff', $input->woffCompress);
			print '(eot) The converter detected other mime file type and/or extension compatible with fontforge ' . $inpFile . ' and is OK.';
			exec($this->fontforge . ' -script '. str_replace('/\/', '/', ROOT) .'/assets/scripts/tosvg.pe "' . $inpFile . '"',
				$output,
				$return
			);	
		}
		else
		{
			exec($this->fontforge . ' -script '. str_replace('/\/', '/', ROOT) .'/assets/scripts/tosvg.pe "' . $inpFile . '"',
				$output,
				$return
			);
		}
		
        if (0 !== $return) 
		{
            throw new \RuntimeException('Fontforge could not convert '.$input->getBasename(). ' ' . $inpFileExt . ' file to SVG format. CMD Line: ' . $this->fontforge . ' -script '. str_replace('/\/', '/', ROOT) .'/assets/scripts/tosvg.pe "' . $inpFile . '"');
        } 
		else 
		{
            return new File($outFile);
        }
    }

    public function getSVGPath(File $input)
    {
        $basename = StringHandler::slugify($input->getBasename('.'.$input->getExtension()));

        return $input->getPath().DIRECTORY_SEPARATOR.$basename.'.svg';
    }
}
