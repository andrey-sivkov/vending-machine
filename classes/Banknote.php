<?php

/**
 * Class Banknote
 */
class Banknote extends Coin
{
    /**
     * @param $amount
     * @return string
     */
    public static function qtyToText($amount)
    {
        $first  = substr($amount, -1);
        $second = strlen($amount) > 1 ? substr($amount, -2, 1) : 0;
        if ($first == 1 && $second != 1)
            return $amount . ' купюра';
        else if (in_array(substr($amount, -1), [2, 3, 4]) && $second != 1)
            return $amount . ' купюры';
        else
            return $amount . ' купюр';
    }
}
