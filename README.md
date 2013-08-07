Jacor's helpers
===============

Those files are supposed to be handy in various projects written in PHP. 
Feel free to use any of them and please tell me if you find a bug.


amountInWords
-------------

This function translates a number into word representation in POLISH language.
It's not as easy as it may seem, because Polish rules for numerals are quite
complex. Typical use case is to describe a total price inside a generated
invoice, so default options cover this case. Another purpose may be to write
an item's weight in grams precisely.

Examples:
``` php
echo amountInWords(1) .PHP_EOL;
// returns: jeden złoty 00/100

echo amountInWords(13579.99) .PHP_EOL;
// returns: trzynaście tysięcy pięćset siedemdziesiąt dziewięć złotych 99/100

echo amountInWords('1001001001.000001', false) .PHP_EOL;
// returns: miliard milion tysiąc jeden i jedna milionowa

echo amountInWords(13579.99, true, array('dolar', 'dolary', 'dolarów')) .PHP_EOL;
// returns: trzynaście tysięcy pięćset siedemdziesiąt dziewięć dolarów 99/100

echo amountInWords('8963.00', false, array('gram', 'gramy', 'gramów', 'grama')) .PHP_EOL;
// returns: osiem tysięcy dziewięćset sześćdziesiąt trzy gramy

echo amountInWords('113000.00102', false, array('gram', 'gramy', 'gramów', 'grama')) .PHP_EOL;
// returns: sto trzynaście tysięcy i sto dwie stutysięczne grama
```

Be careful if you change the function name as it calls itself when 
describing fractions.


