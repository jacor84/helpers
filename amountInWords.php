<?php

    function amountInWords($number, $currency = true, $suffixes = array('złoty', 'złote', 'złotych'))
    {
        $ones = array('zero', 'jeden', 'dwa', 'trzy', 'cztery', 'pięć', 'sześć', 'siedem', 'osiem', 'dziewięć');
        $tens = array('', 'dziesięć', 'dwadzieścia', 'trzydzieści', 'czterdzieści',
                'pięćdziesiąt', 'sześćdziesiąt', 'siedemdziesiąt', 'osiemdziesiąt', 'dziewięćdziesiąt');
        $teens = array('', 'jedenaście', 'dwanaście', 'trzynaście', 'czternaście', 'piętnaście',
                'szesnaście', 'siedemnaście', 'osiemnaście', 'dziewiętnaście');
        $hundreds = array('', 'sto', 'dwieście', 'trzysta', 'czterysta',
                'pięćset', 'sześćset', 'siedemset', 'osiemset', 'dziewięćset');
        $thirds = array(
            3 => array('tysiąc', 'tysiące', 'tysięcy'),
            6 => array('milion', 'miliony', 'milionów'),
            9 => array('miliard', 'miliardy', 'miliardów'),
            12 => array('bilion', 'biliony', 'bilionów'),
            15 => array('biliard', 'biliardy', 'biliardów'),
            18 => array('trylion', 'tryliony', 'trylionów'),
        );

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

		$wholeNumberInWords = ($number < 0)? 'minus ' : '';

		if (!is_string($number)) {
			$number = (string) abs($number);
		}

        $parts = explode('.', $number);
		$integerPart = (int) abs($parts[0]);
		$restInteger = isset($parts[1])? rtrim(substr($parts[1], 0, $maxAccuracy), '0') : 0;

		if ($integerPart == 0 or $integerPart == 1) { // easy (special) case: zero or one
			if (!($integerPart == 0 and $restInteger) or $currency) {
				$wholeNumberInWords .= $ones[$integerPart];
			}

			if ($suffixes and ($currency or !$restInteger)) {
				$wholeNumberInWords .= ' '. $suffixes[$integerPart == 1? 0 : 2];
			}
        }
		else { // hard case
			$unitAdded = false;
			$threes = array();
			while ($integerPart) {
				$threes[] = substr($integerPart, -3);
				$integerPart = substr($integerPart, 0, -3);
			}

			for ($i = 0; isset($threes[$i]); $i++) {
				$amountInWords = '';
				$num = $rest = $threes[$i];

				if ($rest >= 100) { // extract hundrets
					$h = floor($rest / 100);
					$amountInWords .= ' '. $hundreds[$h];
				}
				$rest %= 100;

				if ($rest == 10 or $rest >= 20) { // extract tens
					$t = floor($rest / 10);
					$amountInWords .= ' '. $tens[$t];
					$rest %= 10;
				}
				elseif ($rest >= 10) { // extract teens
					$amountInWords .= ' '. $teens[$rest - 10];
					$rest = 0;
				}

				if ($rest > 0 and !($num == 1 and $i > 0)) { // extract ones
					$amountInWords .= ' '. $ones[$rest];
					$rest = 0;
				}

				if ($num > 0 and isset($thirds[$i*3])) { // add thirds description if necessary
					if ($num == 1) {
						$amountInWords .= ' '. $thirds[$i*3][0];
					}
					elseif ($num >= 10 and $num < 20) {
						$amountInWords .= ' '. $thirds[$i*3][2];
					}
					elseif ($num % 10 > 1 and $num % 10 <= 4) {
						$amountInWords .= ' '. $thirds[$i*3][1];
					}
					else {
						$amountInWords .= ' '. $thirds[$i*3][2];
					}
				}

				if ($suffixes and ($currency or !$restInteger) and !$unitAdded and $num > 0) { // add currency/suffix if desired
					if ($i == 0 and !($num > 10 and $num < 20) and $num % 10 > 1 and $num % 10 <= 4) { // 2-4 ending
						$amountInWords .= ' '. $suffixes[1];
						$unitAdded = true;
					}
					else {
						$amountInWords .= ' '. $suffixes[2]; // all others
						$unitAdded = true;
					}
				}

				$threes[$i] = $amountInWords;
			}

			$wholeNumberInWords .= trim(implode('', array_reverse($threes)), ' ');
		}


		$restPartInWords = '';
		if ($restInteger) {
			if ($currency) {
				$restPartInWords = sprintf(' %02d/100', substr($restInteger, 0, 2));
			}
			else {
				if ($precision = strlen($restInteger)) {
					if (abs($number) >= 1) {
						$restPartInWords = ' i ';
					}
					$units = $fractions[$precision - 1];

					if (isset($suffixes[3])) { // especially for the rest part
						foreach ($units as $i => $unit) {
							$units[$i] .= ' '. $suffixes[3];
						}
					}
					$restPartInWords .= amountInWords($restInteger, false, $units);
					if ($restInteger == 1 or ($restInteger % 10 == 2 and $restInteger % 100 != 12)) {
						$restPartInWords = str_replace(array('jeden', 'dwa'), array('jedna', 'dwie'), $restPartInWords);
					}
				}
			}
		}


		return $wholeNumberInWords . $restPartInWords;
    }

