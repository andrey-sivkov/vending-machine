
/**
 * Показать какое-то сообщение
 *
 * @param message
 * @param type
 * @param autoHide
 */
function showMessage(message, type, autoHide) {
  if (type === undefined)
    type = 'warning';

  if (autoHide === undefined)
    autoHide = true;

  let alert = $('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
      '<span></span><button type="button" class="close" data-dismiss="alert">&times;</button></div>')
  alert.find('span').html(message);
  alert.appendTo('body');
  alert.alert();

  if (autoHide)
    setTimeout("$('.alert').alert('close')", 5000);
}

/**
 * Форматирование числа в строку цены
 *
 * @param price
 * @param decimals
 * @returns {string}
 */
function formatPrice(price, decimals) {
  if (decimals === undefined)
    decimals = 0;

  return parseFloat(price).toFixed(decimals) + ' руб.';
}

/**
 * Получение списка монет
 */
function getCoins() {
  $.ajax({
    type: 'get',
    url: './api/coins-get',
    data: [],
    success: function(coins) {
      $('#coins').empty();
      $.each(coins, function(k, v) {
        $('#coins').append('<button class="btn btn-block btn-info btn-sm mb-1 btn-coin-add" value="' + v + '">' + formatPrice(v) + '</button>');
      });
    },
    error: function() {
      showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

/**
 * Получение списка купюр
 */
function getBanknotes() {
  $.ajax({
    type: 'get',
    url: './api/banknotes-get',
    data: [],
    success: function(banknotes) {
      $('#banknotes').empty();
      $.each(banknotes, function(k, v) {
        $('#banknotes').append('<button class="btn btn-block btn-info btn-sm mb-1 btn-banknote-add" value="' + v + '">' + formatPrice(v) + '</button>');
      });
    },
    error: function() {
      showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

/**
 * Получение списка товаров
 */
function getProducts() {
  $.ajax({
    type: 'get',
    url: './api/products-get',
    data: [],
    success: function(products) {
      $('#products').empty();
      $.each(products, function(k, v) {
        $('#products').append('<button' +
            ' class="btn btn-block btn-success disabled btn-lg mb-1 btn-product-order"' +
            ' value="' + v.id + '"' +
            ' data-name="' + v.name + '"' +
            ' data-qty="' + v.quantity + '"' +
            ' data-price="' + v.price + '"' +
            ' disabled' +
            '>' + v.name + ' (' + v.quantity + ' шт) &nbsp; &nbsp; &nbsp; ' + formatPrice(v.price) +
            '</button>');
      });
    },
    error: function() {
      showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    complete: function() {
      getBalance();
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

/**
 * Получение суммы на счету
 */
function getBalance() {
  $.ajax({
    type: 'get',
    url: './api/balance-get',
    data: [],
    success: function(result) {
      updateInfo(result.balance);
    },
    error: function() {
      showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

/**
 * Обновление информации
 *
 * @param balance
 */
function updateInfo(balance) {
  balance = parseInt(balance);
  $('#balance').empty().append(formatPrice(balance));

  // Если на счету ненулевая сумма, делаем доступной выдачу сдачи
  if (balance > 0)
    $('button.btn-get-change').removeAttr('disabled').show();
  else
    $('button.btn-get-change').attr('disabled', 'disabled').hide();

  $.each($('.btn-product-order'), function() {
    $(this).html($(this).data('name') + ' (' + $(this).data('qty') + ' шт) &nbsp; &nbsp; &nbsp; ' + formatPrice($(this).data('price')));
    // Если на счету ненулевая сумма, делаем доступными для заказа товары стоимость не более этой суммы
    if (parseInt($(this).data('price')) > balance || parseInt($(this).data('qty')) < 1)
      $(this).addClass('disabled').attr('disabled', 'disabled');
    else
      $(this).removeClass('disabled').removeAttr('disabled');
  });
}

/**
 * Добавление монеты в аппарат
 *
 * @param denom
 */
function addCoin(denom) {
  $.ajax({
    type: 'post',
    url: './api/coin-add',
    data: JSON.stringify({
      denom: denom
    }),
    success: function(result) {
      if (result.success)
        updateInfo(result.balance);
      else
        showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    error: function() {
      showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

/**
 * Добавление купюры в автомат
 *
 * @param denom
 */
function addBanknote(denom) {
  $.ajax({
    type: 'post',
    url: './api/banknote-add',
    data: JSON.stringify({
      denom: denom
    }),
    success: function(result) {
      if (result.success)
        updateInfo(result.balance);
      else
        showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    error: function() {
      showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

/**
 * Выдача товара
 *
 * @param productId
 */
function orderProduct(productId) {
  $.ajax({
    type: 'post',
    url: './api/product-order',
    data: JSON.stringify({
      product_id: productId
    }),
    success: function(result) {
      if (result.success) {
        let product = $('button[value="' + productId + '"]');
        product.data('qty', parseInt(product.data('qty')) - 1);
        updateInfo(result.balance);
      } else {
        showMessage('1 Что-то пошло не так, попробуйте повторить попытку');
      }
    },
    error: function() {
      showMessage('2 Что-то пошло не так, попробуйте повторить попытку');
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

/**
 * Выдача сдачи
 */
function getChange() {
  $.ajax({
    type: 'get',
    url: './api/change-get',
    data: [],
    success: function(result) {
      updateInfo(result.balance);
      showMessage(result.change, 'success');
    },
    error: function() {
      showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

/**
 * Возвращение к первоначальным условиям
 */
function restore() {
  $.ajax({
    type: 'get',
    url: './api/restore',
    data: [],
    success: function(result) {
      if (result.success)
        showMessage('Аппарат возвращен в первоначальное состояние', 'success');
      else
        showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    error: function() {
      showMessage('Что-то пошло не так, попробуйте повторить попытку');
    },
    complete: function() {
      getProducts();
    },
    contentType : 'application/json',
    dataType: 'json',
    async: true
  });
}

$(document).ready(function() {
  $.ajaxSetup({
    headers: {'Authorization': 'dklkn42dxdldlk35l2qfcadslm2452ltmfcascmpoo465rmgvasdkhsdasdl3mdl'}
  });

  getCoins();
  getBanknotes();
  getProducts();

  $(document).on('click', '.btn-coin-add', function() {
    addCoin($(this).val());
  });

  $(document).on('click', '.btn-banknote-add', function() {
    addBanknote($(this).val());
  });

  $(document).on('click', '.btn-get-change', function() {
    getChange();
  });

  $(document).on('click', '.btn-product-order', function() {
    orderProduct($(this).val());
  });

  $(document).on('click', '.btn-restore', function() {
    restore();
  });
});