<?php

/**
 * Class Coin
 */
class Coin
{
    private $money_type;

    /**
     * Coin constructor.
     */
    public function __construct()
    {
        $this->setMoneyType('coins');
    }

    /**
     * @param $money_type
     * @return $this
     */
    public function setMoneyType($money_type)
    {
        $this->money_type = $money_type;

        return $this;
    }

    /**
     * @return mixed
     */
    private function getMoneyType()
    {
        return $this->money_type;
    }

    /**
     * Изменение кол-ва монет / купюр в аппарате
     *
     * @param $denom
     * @param $qty
     * @return int
     */
    public function changeQuantity($denom, $qty = 1)
    {
        return DB::coinChangeQuantity($denom, $this->getMoneyType(), $qty);
    }

    /**
     * Добавление монеты / купюры номиналом $denom в аппарат
     *
     * @param $denom
     * @param $qty
     * @return array
     */
    public function add($denom, $qty = 1)
    {
        $result = ['success' => false];

        if ($this->changeQuantity($denom, $qty)) {
            $action  = $this->getMoneyType() == 'banknotes' ? 'banknote-add' : 'coin-add';
            $seance  = new Seance;
            $balance = $seance->changeBalance($denom, $action);

            $result = [
                'success' => true,
                'balance' => $balance
            ];
        }

        return $result;
    }

    /**
     * Список монет / купюр, используемых в работе аппарата
     *
     * @return array
     */
    public function getAll()
    {
        return DB::coinGetAll($this->getMoneyType());
    }

    /**
     * Возвращение к первоначальным условиям:
     * По 100 монет каждого номинала, купюр нет
     *
     * @return int
     */
    public function restore()
    {
        $qty = $this->getMoneyType() == 'banknotes' ? 0 : 100;

        return DB::coinRestore($this->getMoneyType(), $qty);
    }

    /**
     * Приведение количества монет к читабельному виду
     *
     * @param $amount
     * @return string
     */
    public static function qtyToText($amount)
    {
        $first  = substr($amount, -1);
        $second = strlen($amount) > 1 ? substr($amount, -2, 1) : 0;
        if ($first == 1 && $second != 1)
            return $amount . ' монета';
        else if (in_array(substr($amount, -1), [2, 3, 4]) && $second != 1)
            return $amount . ' монеты';
        else
            return $amount . ' монет';
    }

    /**
     * @param $sum
     * @return string
     */
    public static function sumToText($sum)
    {
        return number_format($sum, 0, '.', '') . ' руб.';
    }
}
