<?php
/**
 *
 * https://github.com/jason37/woff2ttf
 * @file WOFF2TTF.php
 * @author of ported version Florin C Bodin
 * Requires PHP >= 5.2 for pathinfo()
 *
 */

namespace WebfontGenerator\Converters;

use WebfontGenerator\Util\StringHandler;
use WebfontGenerator\Converters\Driver;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface ConverterInterface
 *
 * @package WebfontGenerator\Converters
 */
class Woff2TTF
{
    protected $fontforge = null;

    public function __construct($binPath)
    {
		$this->driver = new Driver();
		//$this->woff2ttf = new Woff2TTF();
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
	
    /**
	* Unwraps a  WOFF font file and repackages it as an OpenType font and writes it to disk.
	* @param File string $woffFile    absolute path of WOFF file (includes filename)
	* @param File string $convertPath absolute path to cache converted file (includes filename)
	*/
    public static function convert(File $woffFile)
    {      
        $convertPath = $this->getTFFPath($input);
		

        if (!function_exists('gzuncompress')) 
		{
			throw new \RuntimeException( 'Woff2TTF requires gzuncompress().');
        }
        
        $debug = false;
        $numTables = 0;
        $oTDirEntrySize   = 16; // bytes
        $oTHeaderSize     = 12;
        $woffDirEntrySize = 20;
        $woffHeaderSize   = 44;

        if (!$fh = fopen($woffFile, "rb")) 
		{
            throw new \RuntimeException("Couldn't open file $woffFile.");
        }

        $header = unpack('Nsig/Nflv/Nlen/nntab/nres/Nssize/nmajv/nminv/Nmoff/Nmlen/Nmolen/Nprivo/Nprivl', fread($fh, $woffHeaderSize));

        if ($debug) 
		{
            foreach ($header as $key => $val) 
			{
                printf("%s => 0x%x\n", $key, $val);
            }
        }

        foreach ($header as $key => $val) 
		{
            switch($key) 
			{
                case 'sig':
                    $sfntVersion = $val;
                    if ($debug) 
					{
                        if ($val != 0x774F4646) 
						{ // wOFF
                            if ($debug) echo 'font file not WOFF';
                            throw new \RuntimeException("File is not a valid WOFF font.", $fh);
                        } 
						else 
						{
                            if ($debug) echo 'font file cool.';
                        }
                    }
				break;
                case 'flv':
                    $flavor = $val;
                    if ($val == 0x00010000) 
					{
                        if ($debug) echo 'TrueType flavor.';
                    } else if ($val == 0x4F54544F) 
					{
                        if ($debug) echo 'CFF flavor.';
                    } else {
                        if ($debug) echo 'unknown flavor.';
                    }
                    // Use otf for all flavors, makes deriving the cache filename easier later on.
                    $fileExtension = 'otf';
				break;
                case 'len':
                    if ($debug) echo "\nfile size $val bytes. ";
                    break;
                case 'ntab':
                    if ($debug) echo "\n$val font tables.";
                    $numTables = $val;
				break;
                case 'res':
                    if ($val != 0) {
                        if ($debug) echo "\nproblem - reserved field != 0.";
                        throw new \RuntimeException("Reserved field has to be 0 (zero).", $fh);
                    }
				break;
                case 'ssize':
                    if ($debug) echo "\ntotal data size $val.";
				break;
                case 'majv':
                    if ($debug) echo "\nmajor version $val.";
				break;
                case 'minv':
                    if ($debug) echo "\nminor version $val.";
				break;
                case 'moff':
                    if ($debug) echo "\nmeta offset $val bytes.";
				break;
                case 'mlen':
                    if ($debug) echo "\ncompressed data block $val bytes";
				break;
                case 'molen':
                    if ($debug) echo "\nuncompressed data block $val bytes";
				break;
                case 'privo':
                    if ($debug) echo "\nprivate data offset $val bytes";
                    $privateOffset = $val;
				break;
                case 'privl':
                    if ($debug) echo "\nprivate data length $val bytes";
                    $privateLength = $val;
				break;
            }
        }

        // check out the private data
        if ($debug) 
		{
            $whereAt = ftell($fh);
            fseek($fh, $privateOffset);
            $privateData = fread($fh, $privateLength);
            echo "Private data: $privateData";
            fseek($fh, $whereAt);
        }

        // write offset table
        if ($debug) echo "\nout file $convertPath";
        $ofh = fopen($convertPath, "wb");
        if (!$ofh) 
		{
            throw new \RuntimeException("Couldn't open file $outfile for writing.", $fh);
        }
        fwrite($ofh, pack('N', $flavor));
        fwrite($ofh, pack('n', $numTables));

        $maxPower2 = 0;
        while (pow(2, $maxPower2) <= $numTables) 
		{
            $maxPower2++;
        }

        $searchRange = $maxPower2 * 16;
        $entrySelector = log($searchRange, 2);

        fwrite($ofh, pack('n', $searchRange));
        fwrite($ofh, pack('n', $entrySelector));
        fwrite($ofh, pack('n', $numTables * $searchRange));

        $tableDirectorySize = $oTDirEntrySize * $numTables;

        $tableData = array();
        $tableLength = array();
        $currentEOF = $tableDirectorySize + $oTHeaderSize;



        // Write table records
        for ($i = 0; $i < $numTables; $i++ ) 
		{

            $dirEntry = unpack('Ntag/N4', fread($fh, $woffDirEntrySize));
            if ($debug) {
                printf("\ntag value: %d", $dirEntry['tag']);
                printf("offset: 0x%x\n", $dirEntry[1]);
                printf("compressed size: 0x%x\n", $dirEntry[2]);
                printf("uncompressed size: 0x%x\n", $dirEntry[3]);
                printf("checksum: 0x%x\n", $dirEntry[4]);
            }
            $whereAt = ftell($fh);

            if (fseek($fh, $dirEntry[1])) 
			{
                throw new \RuntimeException("fseek-ing to offset {$dirEntry[1]}", $fh, $ofh);
            }

            $tableData[$i] = fread($fh, $dirEntry[2]);

            fseek($fh, $whereAt);
            if ($dirEntry[2] != $dirEntry[3]) 
			{
                $tableData[$i] = gzuncompress($tableData[$i]);
                $tableLength[$i] = strlen($tableData[$i]);
            } else 
			{
                $tableLength[$i] = $dirEntry[3];
            }

            fwrite($ofh, pack('N', $dirEntry['tag'])); //tag
            fwrite($ofh, pack('N', $dirEntry[4])); // checksum
            fwrite($ofh, pack('N', $currentEOF)); // offset
            fwrite($ofh, pack('N', $tableLength[$i])); // length without padding


            if ($debug) 
			{
                printf("\nwriting table rec for tag: %d\n", $dirEntry['tag']);
                printf("\n\t checksum: 0x%x\n", $dirEntry[4]);
                printf("\n\t offset: %d", $currentEOF);
                printf("\n\t length: %d", $tableLength[$i]);
            }

            $pad = 0;
            if (($tableLength[$i] % 4) != 0) 
			{ // pad for 4-byte boundaries
                $pad = $tableLength[$i] % 4;
            }
            $currentEOF += strlen($tableData[$i]) + $pad;
        }



        $bytesWrote = 0;
        // Write table data
        for ($i = 0; $i < count($tableData); $i++) 
		{
            if ($debug) 
			{
                echo "\nfile pointer at: " . ftell($ofh);
                echo "\ntablelength = " . $tableLength[$i] . ' strlen of data: ' . strlen($tableData[$i]);
            }
            $bytesWrote +=     fwrite($ofh, $tableData[$i], $tableLength[$i]);
            if ($debug) echo "\nwriting {$tableLength[$i]} bytes. Actual total bytes written: $bytesWrote";

            if (($tableLength[$i] % 4) != 0) 
			{ // pad for 4-byte boundaries
               $pad = $tableLength[$i] % 4;
               $bytesWrote += fwrite(($ofh), pack("x$pad"));
                if ($debug) echo "\nwriting $pad null bytes. Actual total bytes written: $bytesWrote";
            }
        }

        fclose($fh);
        fclose($ofh);
    }


    public function getTTFPath(File $input)
    {
        $basename = StringHandler::slugify($input->getBasename('.'.$input->getExtension()));

        return $input->getPath().DIRECTORY_SEPARATOR.$basename.'.tff';
    }
}