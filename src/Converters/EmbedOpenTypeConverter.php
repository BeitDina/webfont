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
 * @file EmbedOpenTypeConverter.php
 * @author Ambroise Maupate
 */
namespace WebfontGenerator\Converters;

use WebfontGenerator\Util\StringHandler;
use WebfontGenerator\Converters\Driver;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class EmbedOpenTypeConverter
 *
 * @package WebfontGenerator\Converters
 */
class EmbedOpenTypeConverter implements ConverterInterface
{
    protected $ttf2eot = null;

    public function __construct($binPath)
    {
		$this->driver = new Driver();
		if (('\\' === \DIRECTORY_SEPARATOR) && (PHP_OS !== 'Linux'))
		{
			//If we use usr/bin for ttf2eot on Windows
			if ($this->driver->file_exists($binPath))
			{
				$this->ttf2eot = $binPath;
			}
			else
			{
				//Rename config-default-win.yml to config.yml
				$this->ttf2eot = $binPath . '.exe';
			}
		}
		else
		{
			//If we use usr/bin for ttf2eot on Linux
			$this->ttf2eot = $binPath;
		}
    }

    public function convert(File $input)
    {
        if (!$this->driver->file_exists($this->ttf2eot))
		{
            throw new \RuntimeException($this->ttf2eot . ' (ttf2eot) could not be found.');
        }

        $output = array([]);
        $outFile = $this->getEOTPath($input);
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
		
 		//If is not a compatible font
		if ((str_replace(array('.TTF', '.ttf'), '.TTF', $inpFile) == $inpFile) || (str_replace(array('.TTF', '.ttf'), '.ttf', $inpFile) == $inpFile) || (str_replace(array('.OTF', '.otf'), '.otf', $inpFile) == $inpFile) || (str_replace(array('.OTF', '.otf'), '.OTF', $inpFile) == $inpFile))
		{
			$inpFile = str_replace(array(ucwords($inpFileExt), $inpFileExt), $fntFileEx, $inpFile);
			
			print 'The converter detected other mime file type and/or extension incompatible with ttf2eot ' . $inpFile . ' and is OK.';
			exec($this->ttf2eot . ' "' . $inpFile . '" > ' . $outFile .'',
				$output,
				$return
			);
		}
		else
		{
			exec($this->ttf2eot . ' "' . $input->getRealPath() . '" > ' . $outFile .'',
				$output,
				$return
			);
		}

        if (0 !== $return)
		{
            throw new \RuntimeException($this->ttf2eot . ' "'.$input->getRealPath() . '"' .
           ' ' . ' could not convert ' . $inpFile .' to EOT format.');
		}
		else
		{
            return new File($outFile);
        }
    }

    public function getEOTPath(File $input)
    {
        $basename = StringHandler::slugify($input->getBasename('.'.$input->getExtension()));

        return $input->getPath().DIRECTORY_SEPARATOR.$basename.'.eot';
    }
}
