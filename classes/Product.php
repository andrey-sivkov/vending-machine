<?php

/**
 * Class Product
 */
class Product
{
    /**
     * Product constructor.
     */
    public function __construct()
    {
    }

    /**
     * Изменение количества товара
     *
     * @param $product_id
     * @param $qty
     * @return mixed
     */
    private function changeQuantity($product_id, $qty = 1)
    {
        return DB::changeProductQuantity($product_id, $qty);
    }

    /**
     * Получение списка всех товаров
     *
     * @return array
     */
    public function getAll()
    {
        return DB::getAllProducts();
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
        $info = DB::getProductInfo($product_id);

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
        $result = ['success' => false];

        // стоимость товара
        $price = $this->getInfo($product_id, 'price');

        $seance = new Seance;

        // остаток на счете
        $balance = $seance->getBalance();

        // цена товара не должна быть больше остатка на счете
        if ($price > $balance)
            return $result;

        // уменьшаем кол-во товара
        if ($this->changeQuantity($product_id, -1)) {
            // уменьшаем остаток на счете
            $balance = $seance->changeBalance(-$price, 'product-order');

            $result = [
                'success' => true,
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
        return DB::restoreProduct();
    }
}
