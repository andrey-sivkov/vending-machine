<?php

/**
 * Class Product
 */
class Product
{
    private $dblink;

    /**
     * Product constructor.
     * @param $dblink
     */
    public function __construct($dblink)
    {
        $this->setDblink($dblink);
    }

    /**
     * @param mixed $dblink
     * @return $this
     */
    private function setDblink($dblink)
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
     * @param $product_id
     * @param $qty
     * @return mixed
     */
    private function changeQuantity($product_id, $qty = 1)
    {
        return $this->getDblink()->query('update products set quantity = quantity + ?d where id = ?d', $qty, $product_id);
    }

    /**
     * Получение списка всех товаров
     *
     * @return array
     */
    public function getAll()
    {
        return $this->getDblink()->query('select * from products');
    }

    /**
     * Получение инфо о конкретном товаре
     * Если указан field, то значение конкретного параметра
     *
     * @param $product_id
     * @param $field
     * @return array|string
     */
    public function getInfo($product_id, $field = '')
    {
        $info = $this->getDblink()->selectRow('select * from products where id = ?d', $product_id);

        return (!empty($field) && isset($info[$field])) ? $info[$field] : $info;
    }

    /**
     * Покупка товара
     *
     * @param $product_id
     * @return array
     */
    public function order($product_id)
    {
        $seance = new Seance($this->getDblink());
        $result = ['success' => false];

        // стоимость товара
        $price = $this->getInfo($product_id, 'price');

        // остаток на счете
        $balance = $seance->getBalance();

        // цена товара не должна быть больше остатка на счете
        if ($price <= $balance) {
            // уменьшаем остаток на счете
            $balance = $seance->changeBalance(-$price);
            // если остаток на счете нулевой, закрываем сеанс
            if ($balance == 0)
                $seance->finish();

            // логируем покупку товара
            $seance->log('product-order', $balance, -$price);

            $result = [
                'success' => $this->changeQuantity($product_id, -1),
                'balance' => $balance
            ];
        }

        return $result;
    }

    /**
     * Возвращение к первоначальным условиям
     *
     * @return bool
     */
    public function restore()
    {
        $query = 'update products set quantity = case 
            when price = 10 then 10 
            when price = 20 then 5 
            when price = 30 then 3 
            when price = 40 then 20 
            when price = 50 then 15 
        end';

        return $this->getDblink()->query($query);
    }
}
