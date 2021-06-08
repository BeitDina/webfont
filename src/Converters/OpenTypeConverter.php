<?php
/**
 * Copyright © 2021, Florin-Ciprian Bodin
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
 * @file TrueTypeConverter.php
 * @author Ambroise Maupate & FlorinCB aka orynider
 */
namespace WebfontGenerator\Converters;

use WebfontGenerator\Util\StringHandler;
use WebfontGenerator\Converters\Driver;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class OpenTypeConverter
 *
 * @package WebfontGenerator\Converters
 */
class OpenTypeConverter implements ConverterInterface
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
        $outFile = $this->getTTFPath($input);
		$inpFile = $input->getRealPath();
		$inpFileExt = substr(strrchr($inpFile, '.'), 1);
		
		$return = 0;
		exec($this->fontforge . ' -script '.ROOT.'/assets/scripts/tootf.pe "'.$inpFile.'"', $output, $return);
		
		if (0 !== $return) 
		{
			$return = 0;
			//Check font flag for TTF and OTF files
			if ((str_replace(array('.TTF', '.ttf'), '.OTF', $inpFile) !== $inpFile) || (str_replace(array('.TTF', '.ttf'), '.otf', $inpFile) !== $inpFile))
			{
				if (!$this->driver->file_exists(str_replace('fontforge', 'ttfpatch', $this->fontforge))) 
				{
					throw new \RuntimeException('Fontforge could not convert '.$input->getBasename().' to TrueType format on ' . PHP_OS . 
					'. Try: ' . str_replace('fontforge', 'ttfpatch', $this->fontforge) . ' ' . ' "'.$inpFile.'" N(0-8)');
				}
				
				$inpFile = str_replace(array('.TTF', '.ttf'), '.ttf', $inpFile);
				exec(str_replace('fontforge', 'ttfpatch', $this->fontforge) . ' "'.$inpFile.'" 0', $output, $return);
				
				if (0 !== $return) 
				{	
					print('Font embedable flag exception (' . $this->driver->file_exists($outFile). '): The converter changed the ' . basename($inpFile) . ' font file flag. Check the License!');
				}
				
				//return new File($outFile);
			}
			elseif ((str_replace(array('.OTF', '.otf'), '.ttf', $inpFile) !== $inpFile) || (str_replace(array('.OTF', '.otf'), '.TTF', $inpFile) !== $inpFile))
			{			
				if (!$this->driver->file_exists(str_replace('fontforge', 'otfccbuild', $this->fontforge))) 
				{
					throw new \RuntimeException('Fontforge could not convert '.$input->getBasename().' to TrueType format on ' . PHP_OS . 
					'. Try: ' . str_replace('fontforge', 'otfccbuild', $this->fontforge) . ' ' . ' "'.$inpFile.'" N(0-8)');
				}
				
				$inpFile = str_replace(array('.OTF', '.otf'), '.otf', $inpFile);
				exec(str_replace('fontforge', 'otfccbuild', $this->fontforge) . ' "'.$inpFile.'"', $output, $return);
				
				if (0 !== $return) 
				{	
					print('Font otf cc build (' . str_replace('fontforge', 'otfccbuild', $this->fontforge) . ' "'.$inpFile.'"' . '): The converter changed the ' . basename($inpFile) . ' font compression.');
				}
				
				//return new File($outFile);
			}
			elseif ((str_replace(array('.WOFF2', '.woff2'), '.ttf', $inpFile) !== $inpFile) || (str_replace(array('.WOFF2', '.woff2'), '.TTF', $inpFile) !== $inpFile))
			{
				//woff2 decompress not supported by fontforge
				print '(woff2 decompress) The converter detected other mime file type and/or extension incompatible with fontforge for ' . $inpFile . ' and is OK.';
				exec(str_replace('fontforge', 'woff2_decompress', $this->fontforge) . ' "'. $inpFile .'"', $output, $return);
				
				if (0 !== $return) 
				{
					print('Font compression exception (' . $this->driver->file_exists($outFile). '): The converter changed the ' . basename($inpFile) . ' font compression.');
				}
			}
			elseif ((str_replace(array('.EOT', '.eot'), '.ttf', $inpFile) !== $inpFile) || (str_replace(array('.EOT', '.eot'), '.TTF', $inpFile) !== $inpFile))
			{
				//woff2 decompress not supported by fontforge
				print '(eot decompress) The converter detected other mime file type and/or extension incompatible with fontforge for ' . $inpFile . ' and is NOT OK.';
				//exec(str_replace('fontforge', 'woff2_decompress', $this->fontforge) . ' "'. $inpFile .'"', $output, $return);
				
				if (0 !== $return) 
				{
					print('Font compression exception (' . $this->driver->file_exists($outFile). '): The converter changed the ' . basename($inpFile) . ' font compression?');
				}
			}
			else
			{
				throw new \RuntimeException('Fontforge could not convert '.$input->getBasename().' from ' . $inpFileExt . ' to TrueType format on ' . PHP_OS . 
				'. Try: ' . $this->fontforge . ' -script '.ROOT.'/assets/scripts/tootf.pe "'.$inpFile.'"');
			} 
        }
		else 
		{
            return new File($outFile);
        }
		
		/*
		exec($this->fontforge . ' -script '.ROOT.'/assets/scripts/tootf.pe "'.$outFile.'"', $output, $return);
		if (0 !== $return) 
		{
			throw new \RuntimeException('Fontforge could not convert '.$input->getBasename().' to TrueType format on ' . PHP_OS . 
			'. Try: ' . $this->fontforge . ' -script '.ROOT.'/assets/scripts/tootf.pe "'.$inpFile.'"');
		} 
		else 
		{
			return new File($outFile);
		}
		*/
    }

    public function getTTFPath(File $input)
    {
        $basename = StringHandler::slugify($input->getBasename('.'.$input->getExtension()));

        return $input->getPath().DIRECTORY_SEPARATOR.$basename.'.otf';
    }
}
