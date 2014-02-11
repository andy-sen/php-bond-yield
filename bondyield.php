<?
/**
 *
    Basic Yield calculation class.
 *
 * I was searching for a PHP class which can cal the YIELD function of Excel. Didn't find any, so wrote one.
 * Thanks Percy for helping derive f'(x) for some of the more complex terms
 *
 * @author    Andy Sen  <email@andysen.com>
 *
**/

class BondYield {

    private function US30360Days($start, $end) //DateTime objects
    {
        $y1 = (int)$start->format('Y');
        $y2 = (int)$end->format('Y');
        $m1 = (int)$start->format('m');
        $m2 = (int)$end->format('m');
        $d1 = (int)$start->format('d');
        $d2 = (int)$end->format('d');

        //1. If D2.M2.Y2 is the last day of February (28 in a non leap year; 29 in a leap year) and
        //D1.M1.Y1 is the last day of February, Set D2 = 30
        if (($d2 == 29 || $d2 == 28) && ($m2 == 2) && ($d1 == 29 || $d1 == 28) && ($m1 == 2)) $d2 = 30;
        //2. If D1 is the last day of February, Set D1 = 30
        if (($d1 == 29 || $d1 == 28) && ($m1 == 2)) $d1 = 30;
        //3. If D2 = 31 and D1 = 30 or 31, Set D2 = 30
        if (($d2 == 31) && ($d1 == 30 || $d1 == 31)) $d2 = 30;
        //4. If D1 = 31, Set D1 = 30
        if ($d1 == 31) $d1 = 30;
        return (360 * ($y2 - $y1) + 30 * ($m2 - $m1) + $d2 - $d1);
    }

    private function getCouponDates($set, $mat, $freq, &$d1, &$d2)
    {

        $period = 'P1Y';
        $d = new DateTime($set->format('Y') . '-' . $mat->format('m-d'));
        $d1 = new DateTime($d->format('Y-m-d')); //because sub is so f*cked up
        $d2 = new DateTime($d->format('Y-m-d'));

        if ($freq == 1) {
            if ($set <= $d) {
                $d1->sub(new DateInterval($period));
                return;
            } else {
                $d2->add(new DateInterval($period));
                return;
            }
        }
        if ($freq == 2) {
            $period = 'P6M';
            $m = (int)$d->format('m');
            if ($m >= 7) {
                $d1->sub(new DateInterval($period));
            } else {
                $d2->add(new DateInterval($period));
            }
            if ($set <= $d1) {
                $d2 = new DateTime($d1->format('Y-m-d'));
                $d1 = new DateTime($d2->format('Y-m-d'));
                $d1->sub(new DateInterval($period));
                return;
            } else
                if ($set > $d1 && $set <= $d2) {
                    return;
                } else {
                    $d1 = new DateTime($d2->format('Y-m-d'));
                    $d2 = new DateTime($d1->format('Y-m-d'));
                    $d2->add(new DateInterval($period));
                    return;
                }
        }
    }

//Return N, DSC , A
    private function getDateParams($set, $mat, $freq = 2)
    {

        $delta = BondYield::US30360Days($set, $mat);
        $p['N'] = floor($delta * $freq / 360) + 1;

        BondYield::getCouponDates($set, $mat, $freq, $cp1, $cp2);
        //	print_r($cp1);	print_r($cp2);
        $p['DSC'] = BondYield::US30360Days($set, $cp2); //$set->diff($cp2)->days;
        $p['A'] = BondYield::US30360Days($cp1, $set); //$cp1->diff($set)->days;
        return ($p);
    }


//Using Newton Raphson method to find yield
//Formula from http://office.microsoft.com/en-in/excel-help/price-HP005209219.aspx
//Day accounting basis is 30/360 US

    function Calculate($sett, $price, $matt, $coupon, $freq = 2, $red = 100)
    {

        $setdate = new DateTime($sett);
        $matdate = new DateTime($matt);

        if ($coupon == 0) {
            return 0;
        }

        if ($freq != 1 && $freq != 2) die ("Frequency can only be 1 or 2");
        $p = BondYield::getDateParams($setdate, $matdate, $freq);
        $n = $p['N'];
        $dsc = $p['DSC'];
        $e = 360 / $freq;
        $a = $p['A'];
//print_r($p);
        if ($n == 1) { //http://office.microsoft.com/en-us/excel-help/yield-HP005209345.aspx
            $t1 = ($red / 100) + $coupon / $freq / 100;
            $t2 = ($price / 100) + ($a / $e * $coupon / $freq / 100);
            $t3 = $freq * $e / $dsc;
            return (($t1 - $t2) * $t3 / $t2 * 100);
        }
        $k1 = $coupon / $freq;
        $k2 = $dsc / $e - 1;
        $k3 = $k1 * $a / $e;

        $i = 0;
        $y0 = $coupon / 100;
        $y1 = $y0;
        $delta = 1;
        while (($delta > 1e-13) && ($i++ < 600)) {

            //print("$y0  ");
            $c1 = pow($y0, $k2 + $n);
            $c2 = pow($y0, $n);
            $fy0 = ($red / $c1) + $k1 * (1 / $c1 * ($c2 - 1) / ($y0 - 1)) - $k3 - $price;

            //Hope you didn't sleep through your calculus classes...
            $t1 = -1 * ($n + $k2) * $red / pow($y0, $n + $k2 + 1);
            $t2 = $k1 / pow($y0, $k2 + $n + 1) / pow($y0 - 1, 2);
            $t3 = (-($k2 + 1) * pow($y0, $n + 1)) + ($k2 * $c2) + (($k2 + $n + 1) * $y0) - ($k2 + $n);
            $dfy0 = $t1 + ($t2 * $t3);
            $y1 = $y0 - ($fy0 / $dfy0);
            $delta = abs($y1 - $y0);
            $y0 = $y1;
            //print ("$y0 \n");
        }
//print("$i\n");
        if ($i == 601) {
            print("Could not find yield even after 600 iterations\n");
            return 0;
        }
        return ($y0 - 1) * $freq * 100;
    }

}
?>
