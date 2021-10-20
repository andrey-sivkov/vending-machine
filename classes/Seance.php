<?php

/**
 * Class Seance
 */
class Seance
{
    private $dblink, $seanceId;

    /**
     * Seance constructor.
     *
     * @param $dblink
     */
    public function __construct($dblink)
    {
        $this->setDblink($dblink)
            ->setId();
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
     * @param $seance_id
     * @return $this
     */
    private function setId($seance_id = null)
    {
        if (!is_null($seance_id)) {
            $this->seanceId = $seance_id;
        } else {
            $seance = $this->getDblink()->selectRow('select id, balance from seances where date_end is null order by id desc limit 1');
            if (empty($seance['id'])) {
                // Нет ни одного активного сеанса, начинаем новый
                $this->seanceId = $this->start();
            } else if ($seance['balance'] === '0') {
                // На счету 0, закрываем предыдущий сеанс и начинаем новый
                $this->finish($seance['id']);
                $this->seanceId = $this->start();
            } else {
                $this->seanceId = $seance['id'];
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    private function getId()
    {
        return $this->seanceId;
    }

    /**
     * Старт сеанса
     * Сеанс начинается с добавления монет/купюр
     *
     * @return int
     */
    public function start()
    {
        $seance_id = $this->getDblink()->query('insert into seances (date_start) values (now())');
        $this->setId($seance_id);

        // логируем старт сеанса
        $this->log('start');

        return $seance_id;
    }

    /**
     * Завершение сеанса
     * Сеанс заканчивается взятием сдачи, а в случае если ее нет, то выдачей продукта
     *
     * @param $seance_id
     * @return bool
     */
    public function finish($seance_id = null)
    {
        if (is_null($seance_id))
            $seance_id = $this->getId();

        // логируем завершение сеанса
        $this->log('finish');

        return $this->getDblink()->query('update seances set date_end = now() where id = ?d and date_end is null', $seance_id);
    }

    /**
     * Сумма на счете покупателя
     *
     * @return int
     */
    public function getBalance()
    {
        return $this->getDblink()->selectCell('select balance from seances where id = ?d', $this->getId());
    }

    /**
     * Добавление / вычитание (в зависимости от знака) суммы на счет покупателя
     *
     * @param $sum
     * @return mixed
     */
    public function changeBalance($sum)
    {
        if ($this->getDblink()->query('update seances set balance = ifnull(balance, 0) + ?d where id = ?d', $sum, $this->getId()))
            return $this->getBalance();

        return false;
    }

    /**
     * Сдача, расчет ее выдачи минимальным количеством монет, завершение сеанса
     *
     * @return array
     */
    public function getChange()
    {
        $balance = $orig_balance = $this->getBalance();
        $result = [
            'coins' => 0,       // кол-во монет в сдаче
            'change' => '',     // описание сдачи
            'balance' => null     // итого на счете
        ];
        $coin = new Coin($this->getDblink());
        $coins = $coin->getAll();
        // отсортируем монеты в порядке убывания их номинала, т.к. чтобы вернуть сдачу минимальным
        // количеством монет, начинать надо с монет бОльшего номинала
        krsort($coins, SORT_NUMERIC);
        foreach ($coins as $denom) {
            // в расчет не берется кол-во монет в аппарате (согласно допущению, его достаточно)
            $amount = floor($balance / $denom);
            if ($amount > 0) {
                $result['coins'] += $amount;
                $result['change'] .= Coin::qtyToText($amount) . ' по ' . Coin::sumToText($denom) . '<br/>' . "\n";
                $balance = $balance%$denom;
                if ($coin->changeQuantity($denom, -$amount))
                    $result['balance'] = $this->changeBalance(-$amount*$denom);
            }
        }
        if ($result['change']) {
            $result['change'] = 'Монет использовано: ' . $result['coins'] .
                '<hr size="1" class="mt-2 mb-1"/>' . $result['change'];

            // логируем выдачу сдачи
            $this->log('change', $result['balance'], $orig_balance);
        }

        // заканчиваем сеанс
        $this->finish();

        return $result;
    }

    /**
     * Добавление записи в лог
     *
     * @param $action
     * @param $balance
     * @param $difference
     * @return int
     */
    public function log($action, $balance = 0, $difference = 0)
    {
        $log = [
            'seance_id' => $this->getId(),
            'action' => $action,
            'balance' => $balance,
            'difference' => $difference
        ];

        return $this->getDblink()->query('insert into logs (?#) values (?a)', array_keys($log), array_values($log));
    }
}
