<?php

/**
 * @author Jacek Symonowicz
 *
 * @param int|double|string $number
 * @param boolean $currency
 * @param array $suffixes
 * @return string
 */
function amountInWords($number, $currency = true, $suffixes = array('złoty', 'złote', 'złotych'))
{
	// define Polish names for numbers: from 0 to 900
	$ones = array('zero', 'jeden', 'dwa', 'trzy', 'cztery',
			'pięć', 'sześć', 'siedem', 'osiem', 'dziewięć');
	$tens = array('', 'dziesięć', 'dwadzieścia', 'trzydzieści', 'czterdzieści',
			'pięćdziesiąt', 'sześćdziesiąt', 'siedemdziesiąt', 'osiemdziesiąt', 'dziewięćdziesiąt');
	$teens = array('', 'jedenaście', 'dwanaście', 'trzynaście', 'czternaście',
			'piętnaście', 'szesnaście', 'siedemnaście', 'osiemnaście', 'dziewiętnaście');
	$hundreds = array('', 'sto', 'dwieście', 'trzysta', 'czterysta',
			'pięćset', 'sześćset', 'siedemset', 'osiemset', 'dziewięćset');

	// define Polish names for thousands, millions etc.
	$thirds = array(
		3 => array('tysiąc', 'tysiące', 'tysięcy'),
		6 => array('milion', 'miliony', 'milionów'),
		9 => array('miliard', 'miliardy', 'miliardów'),
		12 => array('bilion', 'biliony', 'bilionów'),
		15 => array('biliard', 'biliardy', 'biliardów'),
		18 => array('trylion', 'tryliony', 'trylionów'),
	);

	// define Polish names for fractions, used only for non-integers
	$fractions = array(
		array('dziesiąta', 'dziesiąte', 'dziesiątych'),
		array('setna', 'setne', 'setnych'),
		array('tysięczna', 'tysięczne', 'tysięcznych'),
		array('dziesięcznotysięczna', 'dziesięcznotysięczne', 'dziesięcznotysięcznych'),
		array('stutysięczna', 'stutysięczne', 'stutysięcznych'),
		array('milionowa', 'milionowe', 'milionowych'),
		array('dziesięciomilionowa', 'dziesięciomilionowe', 'dziesięciomilionowych'),
		array('stumilionowa', 'stumilionowe', 'stumilionowych'),
		array('miliardowa', 'miliardowe', 'miliardowych'),
		array('dziesięciomiliardowa', 'dziesięciomiliardowe', 'dziesięciomiliardowych'),
		array('stumiliardowa', 'stumiliardowe', 'stumiliardowych'),
		array('bilionowa', 'bilionowe', 'bilionowych'),
	);
	$maxAccuracy = count($fractions);

	// negative number: always start with the 'minus' word
	$wholeNumberInWords = ($number < 0)? 'minus ' : '';

	// the input number could be a number or a string, from now on make it a string
	if (!is_string($number)) {
		$number = (string) abs($number);
	}

	// separate integer and fraction part (if any), transform both to integer numbers
	$parts = explode('.', $number);
	$integerPart = (int) abs($parts[0]);
	$restInteger = isset($parts[1])? rtrim(substr($parts[1], 0, $maxAccuracy), '0') : 0;

	// easy (special) case: zero or one
	if ($integerPart == 0 or $integerPart == 1) {
		// show zero only when in currency mode or there is no rest
		if (!($integerPart == 0 and $restInteger) or $currency) {
			$wholeNumberInWords .= $ones[$integerPart];
		}

		// write an appropriate suffix if there is no rest (fraction) to show or when currency mode
		if ($suffixes and ($currency or !$restInteger)) {
			$wholeNumberInWords .= ' '. $suffixes[$integerPart == 1? 0 : 2];
		}
	}

	// hard case
	else {
		// flag telling that units/suffix has been already placed
		$unitAdded = false;

		// divide the number into groups of three starting from the end
		$threes = array();
		while ($integerPart) {
			$threes[] = substr($integerPart, -3);
			$integerPart = substr($integerPart, 0, -3);
		}

		// process each group of three (almost) the same way, starting from the end
		for ($i = 0; isset($threes[$i]); $i++) {
			$amountInWords = '';
			// $num = current group of (max) three digits, $rest = reminding part to describe
			$num = $rest = $threes[$i];

			// extract hundrets
			if ($rest >= 100) {
				$h = floor($rest / 100);
				$amountInWords .= ' '. $hundreds[$h];
			}
			// get rid of the first digit, if any
			$rest %= 100;

			// extract tens (but not teens), leave the last digit
			if ($rest == 10 or $rest >= 20) {
				$t = floor($rest / 10);
				$amountInWords .= ' '. $tens[$t];
				$rest %= 10;
			}
			// extract teens separately, no reminder to leave
			elseif ($rest >= 10) {
				$amountInWords .= ' '. $teens[$rest - 10];
				$rest = 0;
			}

			// extract ones, but don't show 'one' for thousands, millions etc.
			if ($rest > 0 and !($num == 1 and $i > 0)) {
				$amountInWords .= ' '. $ones[$rest];
				$rest = 0;
			}

			// add thirds description if necessary (thousands, millions etc.)
			if ($num > 0 and isset($thirds[$i*3])) {
				// singular form
				if ($num == 1) {
					$amountInWords .= ' '. $thirds[$i*3][0];
				}
				// 'regular' plural for teens ending
				elseif ($num % 100 >= 10 and $num % 100 < 20) {
					$amountInWords .= ' '. $thirds[$i*3][2];
				}
				// 'special' plural for 2, 3 and 4 endings
				elseif ($num % 10 > 1 and $num % 10 <= 4) {
					$amountInWords .= ' '. $thirds[$i*3][1];
				}
				// 'regular' plural for everything else
				else {
					$amountInWords .= ' '. $thirds[$i*3][2];
				}
			}

			// add the suffix/currency if:
			// 1. it is defined
			// 2. it has not been added yet
			// 3. the number represents currency or is an integer
			// 4. current part (group of up to three digits) was non-zero
			if ($suffixes and !$unitAdded and ($currency or !$restInteger) and $num > 0) {
				// 2-4 ending, but only for the first group of three (less than 1000)
				if ($i == 0 and ($num % 10 > 1 and $num % 10 <= 4) and !($num > 10 and $num < 20)) {
					$amountInWords .= ' '. $suffixes[1];
					$unitAdded = true;
				}
				// all other cases (we handled the singular form before)
				else {
					$amountInWords .= ' '. $suffixes[2];
					$unitAdded = true;
				}
			}

			// replace the three with the word representation (may be empty for 000)
			$threes[$i] = $amountInWords;
		}

		// join the parts of three in the reverse order (from the greatest magnitude)
		$wholeNumberInWords .= trim(implode('', array_reverse($threes)), ' ');
	}


	// take care of the fraction part (if any), but also write '00/100' for currency
	$restPartInWords = '';
	if ($restInteger or $currency) {
		// for the currency purpose: round to two digits and print something like 34/100
		if ($currency) {
			$restPartInWords = sprintf(' %02d/100', substr($restInteger, 0, 2));
		}
		// non-currency: describe exactly
		else {
			// check if there was some integer part, then add the 'and' to join the two parts
			if (abs($number) >= 1) {
				$restPartInWords = ' i ';
			}

			// count the digits required to describe (trailing zeroes were cut out before)
			$precision = strlen($restInteger);

			// choose the appropriate denumerator set
			$units = $fractions[$precision - 1];

			// use the same function to describe the numerator, pass the denumerator set
			$restPartInWords .= amountInWords($restInteger, false, $units);

			// check if there is the fourth form of suffix, especially for the rest part
			// if so, add it to the fraction part description, if not, use the standard plural one
			$restPartInWords .= ' '. (isset($suffixes[3])? $suffixes[3] : $suffixes[2]);

			// finally, replace the '1' form and the '2' ending (but not 12) with another case
			// examples: 'jedna dziesięcznotysięczna', 'sto dwie tysięczne',
			// but: 'tysiąc jeden dziesięciotysięcznych', 'sto dwanaście tysięcznych'
			if ($restInteger == 1) {
				$restPartInWords = preg_replace('/^(.*\s)jeden(\s\w+)$/u', '$1jedna$2', $restPartInWords);
			}
			elseif ($restInteger % 10 == 2 and $restInteger % 100 != 12) {
				$restPartInWords = preg_replace('/^(.*\s*)dwa(\s*\w+)$/u', '$1dwie$2', $restPartInWords);
			}
		}
	}


	return $wholeNumberInWords . $restPartInWords;
}

