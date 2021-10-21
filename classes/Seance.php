<?php

/**
 * Class Seance
 */
class Seance
{
    private $seanceId;

    /**
     * Seance constructor.
     */
    public function __construct()
    {
        $this->setId();
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
            $seance = DB::seanceGetInfo();
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
    private function start()
    {
        $seance_id = DB::seanceStart();
        $this->setId($seance_id);

        // логируем старт сеанса
        $this->addLog('start');

        return $seance_id;
    }

    /**
     * Завершение сеанса
     * Сеанс заканчивается взятием сдачи, а в случае если ее нет, то выдачей продукта
     *
     * @param $seance_id
     * @return bool
     */
    private function finish($seance_id = null)
    {
        if (is_null($seance_id))
            $seance_id = $this->getId();

        // логируем завершение сеанса
        $this->addLog('finish');

        return DB::seanceFinish($seance_id);
    }

    /**
     * Сумма на счете покупателя
     *
     * @return int
     */
    public function getBalance()
    {
        return (int)DB::seanceGetBalance($this->getId());
    }

    /**
     * Добавление / вычитание (в зависимости от знака) суммы на счет покупателя
     *
     * @param $sum
     * @param $action
     * @return mixed
     */
    public function changeBalance($sum, $action)
    {
        if (DB::seanceChangeBalance($this->getId(), $sum)) {
            $balance = $this->getBalance();

            // логируем внесение денег в аппарат
            $this->addLog($action, $balance, $sum);

            // если остаток на счете нулевой, закрываем сеанс
            if ($balance === 0)
                $this->finish();

            return $balance;
        }

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
        $coin = new Coin;
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
                    $result['balance'] = $this->changeBalance(-$amount*$denom, 'get-change');
            }
        }
        if ($result['change']) {
            $result['change'] = 'Монет использовано: ' . $result['coins'] .
                '<hr size="1" class="mt-2 mb-1"/>' . $result['change'];

            // логируем выдачу сдачи
            $this->addLog('change', $result['balance'], $orig_balance);
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
    private function addLog($action, $balance = 0, $difference = 0)
    {
        $log = [
            'seance_id' => $this->getId(),
            'action' => $action,
            'balance' => $balance,
            'difference' => $difference
        ];

        return DB::seanceAddLog($log);
    }
}
