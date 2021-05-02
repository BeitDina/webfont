<?php

namespace WebfontGenerator\Subsetters;

use Symfony\Component\HttpFoundation\File\File;
use WebfontGenerator\Util\StringHandler;

class PythonFontSubset
{
    protected $binPath;

    /**
     * @see http://jrgraphix.net/research/unicode.php
     * @var array
     */
    public static $ranges = [
        'basic_latin' => 'U+0000-007F',
        'latin1_supplement' => 'U+00A0-00FF',
        'latin_extended_a' => 'U+0100-017F',
        'latin_extended_b' => 'U+0180-024F',
        'ipa_extensions' => 'U+0250-02AF',
        'spacing_modifier_letters' => 'U+02B0-02FF',
        'combining_diacritical_marks' => 'U+0300-036F',
        'greek_coptic' => 'U+0370-03FF',
        'cyrillic' => 'U+0400-04FF',
        'cyrillic_supplementary' => 'U+0500-052F',
        'armenian' => 'U+0530-058F',

        
		// …
        'latin_extended_additional' => 'U+1E00-1EFF',
        'greek_extended' => 'U+1F00-1FFF',
		
		//Middle Eastern Scripts - Israely Glyph Pages
        'hebrew' => 'U+0590-05FF',
		'anatolian_hieroglyphs' => 'U+14400-1467F',                  
		'arabic' => 'U+0600-06FF',         
		'arabic_supplement' => 'U+0750-077F',
		'arabic_extended_a' => 'U+08A0-08FF',
		'arabic_presentation_forms_a' => 'U+FB50-FDFF',
		'arabic_presentation_forms_a' => 'U+FE70-FEF',
		'aramaic_imperial' => 'U+10840-1085F',
		'avestan' => 'U+10B00-10B3F',
		'chorasmian' => 'U+10FB0–10FDF',
		'cuneiform' => 'U+12000-123FF',
		'cuneiform_numbers_and_punctuation' => 'U+12400-1247F',
		'early_dynastic_cuneiform' => 'U+12480-1254F',
		'old_persian' => 'U+103A0-103DF',
		'ugaritic' => 'U+10380-1039F',
		'elymaic' => 'U+10FE0-10FFF',
		'hatran' => 'U+108E0-108FF',
		
		'hebrew_presentation_forms' => 'U+FB1D-FB4F',
		'mandaic' 					=> 'U+0840-085F',
		'nabataean' 				=> 'U+10880-108AF',
		'old_north_arabian' 		=> 'U+10A80-10A9F',
		'old_south_arabian' 		=> 'U+10A60-10A7F',
		'pahlavi_inscriptional' 	=> 'U+10B60-10B7F',
		'pahlavi_psalter' 			=> 'U+10B80-10BAF',
		'palmyrene' 				=> 'U+10860-1087F',
		'parthian_inscriptional' 	=> 'U+10B40-10B5F',
		'phoenician' 				=> 'U+10900-1091F',
		'samaritan' 				=> 'U+0800-083F',
		'syriac' 					=> 'U+0700-074F',
		'syriac_supplement' 		=> 'U+0860–086F',
		'yezidi' 					=> 'U+10E80–10EBF',
					
        'thaana' => 'U+0780-07BF',
        'devanagari' => 'U+0900-097F',
        'bengali' => 'U+0980-09FF',
        'gurmukhi' => 'U+0A00-0A7F',
        'gujarati' => 'U+0A80-0AFF',
        'oriya' => 'U+0B00-0B7F',
        'tamil' => 'U+0B80-0BFF',
        'telugu' => 'U+0C00-0C7F',
        'kannada' => 'U+0C80-0CFF',
        'malayalam' => 'U+0D00-0D7F',
        'sinhala' => 'U+0D80-0DFF',
        'thai' => 'U+0E00-0E7F',
        'lao' => 'U+0E80-0EFF',

		// - Southeast Asian Scripts
        'cham' => 'U+AA00-AA5F',
        'hanifi_rohingya' => 'U+10D00-10D3F',
        'kayah_Li' => 'U+A900-A92F',
        'khmer' => 'U+1780-17FF',
        'khmer_symbols' => 'U+19E0-19FF',
        'lao' => 'U+0E80-0EFF',
        'nyanmar' => 'U+1000-109F',
        'myanmar_extended_a' => 'U+AA60-AA7F',
        'myanmar_extended_b' => 'U+A9E0-A9FF',
        'new_tai_lue' => 'U+1980-19DF',
        'nyiakeng_puachue_hmong' => 'U+1E100-1E14F',
        'pahawh_hmong' => 'U+16B00-16B8F',
        'pau_cin_hau' => 'U+11AC0-11AFF',
        'tai_le' => 'U+1950-197F',
        'tai_tham' => 'U+1A20-1AAF',
        'tai_viet' => 'U+AA80-AADF',
        'thai' => 'U+0E00-0E7F',


		// - Indonesia &amp; Oceania Scripts
        'balinese' => 'U+1B00-1B7F',
        'batak' => 'U+1BC0-1BFF',
        'buginese' => 'U+1A00-1A1F',
        'buhid' => 'U+1740-175F',
        'hanunoo' => 'U+1720-173F',
        'javanese' => 'U+A980-A9DF',
        'makasar' => 'U+11EE0-11EFF',
        'rejang' => 'U+A930-A95F',
        'sundanese' => 'U+1B80-1BBF',
        'sundanese Supplement' => 'U+1CC0-1CCF',
        'tagalog' => 'U+1700-171F',
        'tagbanwa' => 'U+1760-177F',

		// - East Asian Scripts
		'bopomofo' 					=> 'U+3100-312F',
        'bopomofo_extended' 		=> 'U+31A0-31BF',
		'CJK_unified_ideographs_(han)' 	=> 'U+4E00-9FFF',
		'CJK_extension_a'			=> 'U+3400-4DBF',
		'CJK_extension_b' 			=> 'U+20000-2A6DF',
		'CJK_extension_c' 			=> 'U+2A700-2B73F',
		'CJK_extension_d' 			=> 'U+2B740-2B81F',
		'CJK_extension_e' 			=> 'U+2B820-2CEAF',
		'CJK_extension_f' 			=> 'U+2CEB0–2EBE0',
		'CJK_extension_g' 			=> 'U+30000–3134A',
		
		//Unihan Database unicode.org/unihan.html
		'CJK_compatibility_ideographs' 				=> 'U+F900-FAFF',
		'CJK_compatibility_ideographs_supplement' 	=> 'U+2F800-2FA1F',
		'CJK_radicals_kangxi_radicals' 			=> 'U+2F00-2FDF',
        'CJK_radicals_supplement' 					=> 'U+2E80-2EFF',
        'CJK_strokes' 								=> 'U+31C0-31EF',
		'ideographic_description_characters' 		=> 'U+2FF0-2FFF',
        
		// - Korean
		'hangul_jamo' 						=> 'U+1100-11FF',
        'hangul_jamo_extended_a' 			=> 'U+A960-A97F',
        'hangul_jamo_extended_b' 			=> 'U+D7B0-D7FF',
        'hangul_compatibility_jamo' 		=> 'U+3130-318F',
        'halfwidth_jamo' 					=> 'U+FFA0-FFDC',
		'hangul_syllables' 					=> 'U+AC00-D7AF',
        
		// - Japanese
		'hiragana' 					=> 'U+3040-309F',
        'kana_extended_a' 			=> 'U+1B100–1B12F',
        'kana_supplement' 			=> 'U+1B000-1B0FF',
        'small_kana_extension' 		=> 'U+1B130-1B16F',
        'kanbun' 					=> 'U+3190-319F',
        'katakana' 					=> 'U+30A0-30FF',
        'katakana_phonetic_extensions' => 'U+31F0-31FF',
        'halfwidth_katakana' 		=> 'U+FF65-FF9F',
        'khitan_small_script' 		=> 'U+18B00–18CFF',
        'lisu' 						=> 'U+A4D0-A4FF',
        'lisu_supplement' 			=> 'U+11FB0–11FBF',
        'miao' 						=> 'U+16F00-16F9F',
        'nushu' 					=> 'U+1B170–1B2FF',
        'tangut' 					=> 'U+17000-187FF',
        'tangut_components' 		=> 'U+18800-18AFF',
        'tangut_supplement' 		=> 'U+18D00–18D08',
        
		//Yi
        'yi_syllables' => 'U+A000-A48F',
        'yi_radicals' => 'U+A490-A4CF',
		
		// -Central Asian Scripts
        'manichaean' => 'U+10AC0-10AFF',
        'marchen' => 'U+11C70-11CBF',
        'mongolian' => 'U+1800-18AF',
        'mongolian_supplement' => 'U+11660-1167F',
        'old_sogdian' => 'U+10F00-10F2F',
        'old_turkic' => 'U+10C00-10C4F',
        'phags-pa' => 'U+A840-A87F',
        'sogdian' => 'U+10F30-10F6F',
        'soyombo' => 'U+11A50–11AAF',
        'tibetan' => 'U+0F00-0FFF',
        'zanabazar_square' => 'U+11A00–11A4F',
		
		// -
        'general_punctuation' => 'U+2000-206F',
        'superscripts_subscripts' => 'U+2070-209F',
        'currency_symbols' => 'U+20A0-20CF',
        
		//…
        'number_forms' => 'U+2150-218F',
        'arrows' => 'U+2190-21FF',
        'mathematical_operators' => 'U+2200-22FF',
    ];

    public function __construct($binPath)
    {
		//If we use usr/bin for pyftsubset on Linux
		$this->binPath = $binPath;	
    }

    /**
     * @return array
     */
    public static function getBaseSet(): array
    {
        return [
            static::getUnicodes('basic_latin'),
            static::getUnicodes('latin1_supplement'),
            static::getUnicodes('latin_extended_a'),
            static::getUnicodes('latin_extended_b'),
            static::getUnicodes('ipa_extensions'),
            static::getUnicodes('spacing_modifier_letters'),
            static::getUnicodes('combining_diacritical_marks'),
            static::getUnicodes('greek_coptic'),
            static::getUnicodes('cyrillic'),
            static::getUnicodes('cyrillic_supplementary'),
            static::getUnicodes('armenian'),
           
		   // -
			static::getUnicodes('hebrew'),
            static::getUnicodes('anatolian_hieroglyphs'),                  
            //static::getUnicodes('arabic'),         
            //static::getUnicodes('arabic_supplement'),
            //static::getUnicodes('arabic_extended_a'),
            //static::getUnicodes('arabic_presentation_forms_a'),
            //static::getUnicodes('arabic_presentation_forms_a'),
            static::getUnicodes('aramaic_imperial'),
            // static::getUnicodes('avestan'),
            //static::getUnicodes('chorasmian'),
            static::getUnicodes('cuneiform'),
            //static::getUnicodes('cuneiform_numbers_and_punctuation'),
            //static::getUnicodes('early_dynastic_cuneiform'),
            //static::getUnicodes('old_persian'),
            //static::getUnicodes('ugaritic'),
            //static::getUnicodes('elymaic'),
            //static::getUnicodes('hatran'),
		
            static::getUnicodes('hebrew_presentation_forms'),
            static::getUnicodes('mandaic'),
            static::getUnicodes('nabataean'),
            //static::getUnicodes('old_north_arabian'),
            //static::getUnicodes('old_south_arabian'),
            //static::getUnicodes('pahlavi_inscriptional'),
            //static::getUnicodes('pahlavi_psalter'),
            //static::getUnicodes('palmyrene'),
            //static::getUnicodes('parthian_inscriptional'),
            static::getUnicodes('phoenician'),
            static::getUnicodes('samaritan'),
            static::getUnicodes('syriac'),
            static::getUnicodes('syriac_supplement'),
			
			static::getUnicodes('yezidi'),
            static::getUnicodes('thaana'),
            static::getUnicodes('devanagari'),
            static::getUnicodes('bengali'),
            static::getUnicodes('gurmukhi'),
            static::getUnicodes('gujarati'),
            static::getUnicodes('oriya'),
            static::getUnicodes('tamil'),
            static::getUnicodes('telugu'),
            static::getUnicodes('kannada'),

            //static::getUnicodes('manichaean'),
            //static::getUnicodes('marchen'),
            static::getUnicodes('mongolian'),
            static::getUnicodes('mongolian_supplement'),
            //static::getUnicodes('old_sogdian'),
            static::getUnicodes('old_turkic'),
            //static::getUnicodes('phags-pa'),
            //static::getUnicodes('sogdian'),
            //static::getUnicodes('soyombo'),
            static::getUnicodes('tibetan'),
            //static::getUnicodes('zanabazar_square'),
			
			// - Southeast Asian Scripts</p>


			static::getUnicodes('malayalam'),
            static::getUnicodes('sinhala'),
            static::getUnicodes('thai'),
            static::getUnicodes('lao'),
 		
			// - East Asian Scripts
			//static::getUnicodes('bopomofo'),
			//static::getUnicodes('bopomofo_extended'),
			static::getUnicodes('CJK_unified_ideographs_(han)'),
			static::getUnicodes('CJK_extension_a'),
			static::getUnicodes('CJK_extension_b'),
			static::getUnicodes('CJK_extension_c'),
			static::getUnicodes('CJK_extension_d'),
			static::getUnicodes('CJK_extension_e'),
			static::getUnicodes('CJK_extension_f'),
			static::getUnicodes('CJK_extension_g'),
		
			//Unihan Database unicode.org/unihan.html
			static::getUnicodes('CJK_compatibility_ideographs'),
			static::getUnicodes('CJK_compatibility_ideographs_supplement'),
			static::getUnicodes('CJK_radicals_kangxi_radicals'),
			static::getUnicodes('CJK_radicals_supplement'),
			static::getUnicodes('CJK_strokes'),
			static::getUnicodes('ideographic_description_characters'),
				
			// …
            static::getUnicodes('latin_extended_additional'),
            static::getUnicodes('greek_extended'),
            static::getUnicodes('general_punctuation'),
            static::getUnicodes('superscripts_subscripts'),
            static::getUnicodes('currency_symbols'),
			
			//…
            static::getUnicodes('number_forms'),
            static::getUnicodes('arrows'),
            static::getUnicodes('mathematical_operators'),
        ];
    }

    /**
     * @param string $set
     *
     * @return mixed
     */
    public static function getUnicodes(string $set): string
    {
        if (!array_key_exists($set, static::$ranges)) 
		{
            throw new \InvalidArgumentException('Unicode range does not exist: ' . $set . '.');
        }
        return static::$ranges[$set];
    }

    /**
     * @param File  $input
     * @param array $unicodes
     *
     * @return File
     */
	 public function subset(File $input, array $unicodes = [])
	 {
		if (!file_exists($this->binPath))
		{
			//throw new \RuntimeException('pyftsubset binary could not be found at path ' . $this->binPath);
		}
		$outFile = $this->getSubsetPath($input);
		
		if (count($unicodes) === 0) 
		{
			$unicodes = $this->getBaseSet();
		}
		
		$cmd = $this->binPath . ' "'.$input->getRealPath().'" --unicodes="'. implode(',', $unicodes).'" --output-file="'.$outFile.'"';

        exec(
            $cmd,
            $output,
            $return
        );

        if (0 !== $return) 
		{
            throw new \RuntimeException('pyftsubset could not subset '.$input->getBasename().' font file.');
        } 
		else 
		{
            return new File($outFile);
        }
    }

    /**
     * @param File $input
     *
     * @return string
     */
    public function getSubsetPath(File $input)
    {
        $basename = StringHandler::slugify($input->getBasename('.'.$input->getExtension()));

        return $input->getPath().DIRECTORY_SEPARATOR.$basename.'-subset.'.$input->getExtension();
    }
}
