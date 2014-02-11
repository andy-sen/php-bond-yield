php-bond-yield
==============

Bond Yield calculation class

I was searching for a PHP class which can calculate the YIELD function of Excel. Didn't find any, so wrote one.
Calculates the bond yield using the Newton Raphson method
Formula from http://office.microsoft.com/en-in/excel-help/price-HP005209219.aspx

For maturity dates less than 1 year, see http://office.microsoft.com/en-us/excel-help/yield-HP005209345.aspx

Currently it only handles coupon frequency of 1 or 2 per year and day accounting basis of 30/360 US


### Usage: 

```php

require_once 'bondyield.php';
printf("%f\n",BondYield::Calculate('2001-12-16',99, '2002-01-15',5,2,100));
```


parameters     | description
---------------|--------------
settlement date| in yyyy-mm-dd format
current price | in currency units
maturity date | in yyyy-mm-dd format
coupon rate | in percentage. Eg for 5.5% enter 5.5
coupon frequency per year | 1 or 2 (annual or semi-annual)
redemption value |usually 100, same currency unit at price 
 
