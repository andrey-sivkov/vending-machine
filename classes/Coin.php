<?php

/**
 * Class Coin
 */
class Coin
{
    private $dblink;
    protected $dbtable;

    /**
     * Coin constructor.
     *
     * @param $dblink
     */
    public function __construct($dblink)
    {
        $this->setDblink($dblink);
        $this->dbtable = get_parent_class($this) ? 'banknotes' : 'coins';
    }

    /**
     * @param mixed $dblink
     * @return $this
     */
    protected function setDblink($dblink)
    {
        $this->dblink = $dblink;

        return $this;
    }

    /**
     * @return mixed
     */
    private function getDblink()
    {
        return $this->dblink;
    }

    /**
     * Изменение кол-ва монет / купюр в аппарате
     *
     * @param $denom
     * @param $qty
     * @return mixed
     */
    public function changeQuantity($denom, $qty = 1)
    {
        return $this->getDblink()->query('update ?# set quantity = quantity + ?d where denom = ?d', $this->dbtable, $qty, $denom);
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
            $seance = new Seance($this->getDblink());
            $balance = $seance->changeBalance($denom);

            // логируем внесение денег в аппарат
            $seance->log('coin-add', $balance, $denom);

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
        return $this->getDblink()->selectCol('select denom from ?# order by denom', $this->dbtable);
    }

    /**
     * Возвращение к первоначальным условиям:
     * По 100 монет каждого номинала, купюр нет
     *
     * @return int
     */
    public function restore()
    {
        $qty = $this->dbtable == 'banknotes' ? 0 : 100;

        return $this->getDblink()->query('update ?# set quantity = ?d', $this->dbtable, $qty);
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
