<?
require_once 'bondyield.php'; 
//parameters: settlement date, price, maturity date, coupon rate, frequency of coupon payment, redemption value
printf("%f\n",BondYield::Calculate('2001-12-16',99, '2002-01-15',5,2,100));
//print mktime(0, 0, 0, 3, 31,  2038 );
?>
