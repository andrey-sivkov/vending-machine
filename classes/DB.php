<?php

/**
 * Class DB
 */
class DB
{
    private $dblink;
    private static $instance = null;

    /**
     * @return DB|null
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            $db = new DB;
            self::$instance = $db->getDblink();
        }

        return self::$instance;
    }

    /**
     * DB constructor.
     */
    private function __construct()
    {
        require_once __DIR__ . '../../vendor/DbSimple/Generic.php';
        $dsn = 'mysqli://' . DB_SERVER_USERNAME . ':' . DB_SERVER_PASSWORD . '@' . DB_SERVER . '/' . DB_DATABASE;
        $dblink = DbSimple_Generic::connect($dsn);
        $this->setDblink($dblink);
    }

    /**
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
     * @param $money_type
     * @param $qty
     * @return int
     */
    public static function coinChangeQuantity($denom, $money_type, $qty = 1)
    {
        return self::getInstance()->query('update ?# set quantity = quantity + ?d where denom = ?d', $money_type, $qty, $denom);
    }

    /**
     * Получение списка монет / купюр, используемых в работе аппарата
     *
     * @param $money_type
     * @return array
     */
    public static function coinGetAll($money_type)
    {
        return self::getInstance()->selectCol('select denom from ?# order by denom', $money_type);
    }

    /**
     * Возвращение к первоначальным условиям:
     * По 100 монет каждого номинала, купюр нет
     *
     * @param $money_type
     * @param $qty
     * @return int
     */
    public static function coinRestore($money_type, $qty = 0)
    {
        return self::getInstance()->query('update ?# set quantity = ?d', $money_type, $qty);
    }

    /**
     * Изменение количества товара
     *
     * @param $product_id
     * @param $qty
     * @return int
     */
    public static function productChangeQuantity($product_id, $qty)
    {
        return self::getInstance()->query('update products set quantity = quantity + ?d where id = ?d', $qty, $product_id);
    }

    /**
     * Получение списка всех товаров
     *
     * @return array
     */
    public static function productGetAll()
    {
        return self::getInstance()->query('select * from products');
    }

    /**
     * Получение инфо о конкретном товаре
     *
     * @param $product_id
     * @return array
     */
    public static function productGetInfo($product_id)
    {
        return self::getInstance()->selectRow('select * from products where id = ?d', $product_id);
    }

    /**
     * Возвращение к первоначальным условиям
     *
     * @return bool
     */
    public static function productRestore()
    {
        $query = 'update products set quantity = case 
            when price = 10 then 10 
            when price = 20 then 5 
            when price = 30 then 3 
            when price = 40 then 20 
            when price = 50 then 15 
        end';

        return self::getInstance()->query($query);
    }

    /**
     * Получение инфо о текущем сеансе
     *
     * @return array
     */
    public static function seanceGetInfo()
    {
        return self::getInstance()->selectRow('select id, balance from seances where date_end is null order by id desc limit 1');
    }

    /**
     * Получение id нового сеанса
     *
     * @return int
     */
    public static function seanceStart()
    {
        return self::getInstance()->query('insert into seances (date_start) values (now())');
    }

    /**
     * Завершение сеанса
     *
     * @param $seance_id
     * @return int
     */
    public static function seanceFinish($seance_id)
    {
        return self::getInstance()->query('update seances set date_end = now() where id = ?d and date_end is null', $seance_id);
    }

    /**
     * Получение баланса сеанса
     *
     * @param $seance_id
     * @return int
     */
    public static function seanceGetBalance($seance_id)
    {
        return self::getInstance()->selectCell('select balance from seances where id = ?d', $seance_id);
    }

    /**
     * Изменение баланса сеанса
     *
     * @param $seance_id
     * @param $sum
     * @return int
     */
    public static function seanceChangeBalance($seance_id, $sum)
    {
        return self::getInstance()->query('update seances set balance = ifnull(balance, 0) + ?d where id = ?d', $sum, $seance_id);
    }

    /**
     * Изменение баланса сеанса
     *
     * @param $data
     * @return int
     */
    public static function seanceAddLog($data)
    {
        return self::getInstance()->query('insert into logs (?#) values (?a)', array_keys($data), array_values($data));
    }
}
