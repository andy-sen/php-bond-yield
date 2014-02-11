php-bond-yield
==============

Bond Yield calculation class

I was searching for a PHP class which can cal the YIELD function of Excel. Didn't find any, so wrote one.
Calculates the bond yeild using the Newton Raphson method
Formula from http://office.microsoft.com/en-in/excel-help/price-HP005209219.aspx
Day accounting basis is 30/360 US

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
 
