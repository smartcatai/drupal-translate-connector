/*
 * Фронт страницы статистики
 */
jQuery(function ($) {


  function cl(message) {
    console.log(message);
  }

  function printError(message) {
    $(".translation-connectors-statistics-error").remove();
    $(".content").prepend('<div class="translation-connectors-statistics-error messages error">' + message + '</div>');
  }

  var refreshStatButton = $('.smartcat-connector-refresh-statistics');
  var intervalTimer;
  var isStatWasStarted = false;

  function checkStatistics() {
    $.ajax({
      type: "POST",
      url: '/admin/config/regional/translation_connectors/statistics/check',
      success: function (responseJSON) {
        cl('SUCCESS');
        var isActive = responseJSON.data.statistic_queue_active;

        if (!isActive) {
          clearInterval(intervalTimer);
          isStatWasStarted = false;
          window.location.reload();
        }
      },
      error: function (responseObject) {
        cl('ERROR');
        var responseJSON = JSON.parse(responseObject.responseText);
        printError(responseJSON.message);

        if (intervalTimer) {
          clearInterval(intervalTimer);
        }

        refreshStatButton.attr('disabled', false);
        refreshStatButton.removeClass('form-button-disabled');
      }
    });
  }

  function updateStatistics() {
    $.ajax({
      type: "POST",
      url: '/admin/config/regional/translation_connectors/statistics/start',
      success: function (responseJSON) {
        cl('SUCCESS');
        cl(responseJSON);

        if (responseJSON.message === 'ok') {
          if (!intervalTimer) {
            intervalTimer = setInterval(checkStatistics, 5000);
          }
        }
      },
      error: function (responseObject) {
        cl('ERROR');
        var responseJSON = JSON.parse(responseObject.responseText);
        printError(responseJSON.message);
      }
    });
  }

//проверяем на существование, что мы точно на странице статистики
  if (refreshStatButton.length) {
    isStatWasStarted = refreshStatButton.is(':disabled');

    refreshStatButton.click(function (event) {
      //если уже получаем статистику - ничего не делать
      if (isStatWasStarted) {
        event.preventDefault();
        return false;
      }

      isStatWasStarted = true;
      var $this = $(this);
      refreshStatButton.attr('disabled', true);
      refreshStatButton.addClass('form-button-disabled');

      updateStatistics();

      event.preventDefault();
      return false;
    });

    //если статистика была запущена уже в первый запуск
    if (isStatWasStarted) {
      intervalTimer = setInterval(checkStatistics, 5000);
    }
  }
});