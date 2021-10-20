<?php

use DbSimple_Mysqli as DB;

/**
 * Class API
 */
class API
{
    private $dblink,
        $method,
        $action,
        $params,
        $coin,
        $banknote,
        $product,
        $seance,
        $token;

    /**
     * API constructor.
     *
     * @param $dblink
     */
    public function __construct($dblink)
    {
        $this->setDblink($dblink)
            ->setToken()
            ->setMethod($_SERVER['REQUEST_METHOD'])
            ->setAction($_GET['action'])
            ->setParams()
            ->setCoin()
            ->setBanknote()
            ->setProduct()
            ->setSeance();
    }

    /**
     * @param mixed $dblink
     * @return $this
     */
    public function setDblink($dblink)
    {
        $this->dblink = $dblink;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDblink()
    {
        return $this->dblink;
    }

    /**
     * @return $this
     */
    public function setToken()
    {
        $headers = array_change_key_case(getallheaders());
        $this->token = isset($headers['authorization']) ? $headers['authorization'] : null;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Проверка токена авторизации
     * @param $token
     * @return string
     */
    public function checkToken($token)
    {
        return $this->getToken() === $token;
    }

    /**
     * @param $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = strtolower($method);

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return $this
     */
    public function setParams()
    {
        if ($this->getMethod() == 'post')
            $request = file_get_contents('php://input');
        else
            $request = preg_match('/^[^\{]*(\{.*\})[^\}]*$/', urldecode($_SERVER['QUERY_STRING']), $matches) ?
                $matches[1] : '[]';

        $this->params = json_decode($request, true);

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return $this
     */
    public function setCoin()
    {
        $this->coin = new Coin($this->getDblink());

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCoin()
    {
        return $this->coin;
    }

    /**
     * @return $this
     */
    public function setBanknote()
    {
        $this->banknote = new Banknote($this->getDblink());
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBanknote()
    {
        return $this->banknote;
    }

    /**
     * @return $this
     */
    public function setProduct()
    {
        $this->product = new Product($this->getDblink());

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return $this
     */
    public function setSeance()
    {
        $this->seance = new Seance($this->getDblink());

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeance()
    {
        return $this->seance;
    }

    /**
     * @return mixed
     */
    public function getAllCoins()
    {
        return $this->getCoin()->getAll();
    }

    /**
     * @param $denom
     * @return mixed
     */
    public function addCoin($denom)
    {
        return $this->getCoin()->add($denom);
    }

    /**
     * @return mixed
     */
    public function getAllBanknotes()
    {
        return $this->getBanknote()->getAll();
    }

    /**
     * @param $denom
     * @return mixed
     */
    public function addBanknote($denom)
    {
        return $this->getBanknote()->add($denom);
    }

    /**
     * @return mixed
     */
    public function getAllProducts()
    {
        return $this->getProduct()->getAll();
    }

    /**
     * @param $product_id
     * @return array
     */
    public function orderProduct($product_id)
    {
        return $this->getProduct()->order($product_id);
    }

    /**
     * @return array
     */
    public function getChange()
    {
        return $this->getSeance()->getChange();
    }

    /**
     * @return array
     */
    public function getBalance()
    {
        return ['balance' => (int)$this->getSeance()->getBalance()];
    }

    /**
     * @return array
     */
    public function restore()
    {
        $success = is_numeric($this->getCoin()->restore())
            && is_numeric($this->getBanknote()->restore())
            && is_numeric($this->getProduct()->restore());

        return ['success' => $success];
    }
}
