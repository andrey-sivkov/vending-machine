<?php

error_reporting(E_ALL & ~E_NOTICE);

// настройки
require_once '../config/config.php';

// автозагрузка класса
spl_autoload_register(function($class) {
    if (file_exists($class_filename = '../classes/' . $class . '.php'))
        include $class_filename;
});

$api = new API;

if (!$api->checkToken(AUTH_TOKEN)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
    $result = ['success' => false];
    goto end;
}

switch ($api->getAction()) {
    // получение списка монет
    case 'coins-get':
        $result = $api->getAllCoins();
        break;

    // получение списка купюр
    case 'banknotes-get':
        $result = $api->getAllBanknotes();
        break;

    // получение списка товаров
    case 'products-get':
        $result = $api->getAllProducts();
        break;

    // внесение монеты определенного номинала
    case 'coin-add':
        $result = $api->addCoin($api->getParams()['denom']);
        break;

    // внесение купюры определенного номинала
    case 'banknote-add':
        $result = $api->addBanknote($api->getParams()['denom']);
        break;

    // покупка товара
    case 'product-order':
        $result = $api->orderProduct($api->getParams()['product_id']);
        break;

    // получение сдачи
    case 'change-get':
        $result = $api->getChange();
        break;

    // получение сводной информации
    case 'balance-get':
        $result = $api->getBalance();
        break;

    // возвращение аппарата к первоначальному состоянию
    case 'restore':
        $result = $api->restore();
        break;

    // действие не определено
    default:
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not found', true, 404);
        $result = ['success' => false];
        break;
}

end:
echo json_encode($result, JSON_UNESCAPED_UNICODE);
