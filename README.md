# Модуль Recache для Bitrix

[![Claramente](https://claramente.ru/upload/claramente/a2c/ho3rj4p3j2t7scsartohgjajkb1xkyh0/logo.svg)](https://claramente.ru)


Установка через composer
-------------------------
Пример composer.json с установкой модуля в local/modules/
```
{
  "extra": {
    "installer-paths": {
      "local/modules/{$name}/": ["type:bitrix-module"]
    }
  },
  "require": {
    "claramente/claramente.recache": "dev-master"
  }
}
```

1. Запустить `composer require claramente/claramente.recache dev-master`

2. В административном разделе установить модуль **claramente.recache** _(/bitrix/admin/partner_modules.php?lang=ru)_

3. После установки модуля он будет доступен в разделе Сервисы => Регенерация кэширования (_/bitrix/admin/claramente_recache.php?lang=ru_)

# Модуль регенерации кэша

Данный модуль позволяет автоматически регенерировать кэш на сайте, используя ссылки из карты сайта (sitemap). Это улучшает производительность сайта и обеспечивает актуальность отображаемого контента.

---

## Описание возможностей

- **Автоматическая регенерация кэша** на основе sitemap.
- **Поддержка нескольких сайтов** с индивидуальными настройками.
- **Мониторинг процесса регенерации** с отображением статуса и кода ответа.
- **Гибкие настройки таймаутов и интервалов** для оптимизации нагрузки на сервер.

---

## Настройки модуля

Чтобы настроить модуль, перейдите в раздел:

**Сервисы > Генерация кэширования > Настройки модуля**

Здесь необходимо выполнить следующие действия:

1. **Убедитесь, что агент установлен**:
    - Статус: **Агент создан: да**
2. **Активируйте регенерацию для нужного сайта**:
    - Поставьте галочку напротив **"✅ Включить для сайта"**.
3. **Добавьте ссылки на sitemap**:
    - Укажите одну или несколько ссылок на sitemap через запятую.
4. **Убедитесь, что агенты работают на кроне**:
    - Для работы модуля необходимо чтобы агенты работали на кроне, иначе загрузка страниц будет выполняться с ожиданием на клиенсткой стороне.

---

## Проверка работы модуля

Чтобы проверить работу модуля, выполните следующие действия:

1. Перейдите в раздел **"Страницы регенерации"**.
2. Нажмите на кнопку **"Регенерировать все страницы"**.
3. При успешной регенерации будет отображён соответствующий статус.  
   В случае ошибки будет указано её описание.

**Мониторинг процесса** можно выполнять на странице **"Страницы регенерации"**, отслеживая статус и коды ответов.

---

## Страницы регенерации

На странице "Страницы регенерации" отображаются:
- Статусы выполнения запросов
- Коды ответов от сервера
- Обновляемые ссылки

![Страницы регенерации](https://claramente.ru/upload/claramente/claramente-recache-module-page-pages.png)

---

## Страницы настроек

На странице "Настройки модуля" представлены параметры конфигурации для каждого сайта.

![Страницы настроек](https://claramente.ru/upload/claramente/claramente-recache-module-page-settings.png)

### Описание параметров

- **✅ Включить для сайта** – Активность регенерации для выбранного сайта.
- **🔗 Ссылки на sitemap** – Ссылки на sitemap через запятую, если их несколько.
- **📅 Последний запуск регенерации** – Дата и время последнего запуска регенерации всех ссылок.
- **🔁 Интервал принудительного автоматического запуска регенерации** – Укажите интервал в минутах. Раз в N минут агент будет пересоздавать страницы и загружать их.
- **🚦 Количество запросов для обработки за цикл** – Количество страниц, загружаемых за один цикл (раз в минуту).
- **⏳️ Таймаут запроса** – Максимальное время ожидания ответа от сервера в секундах. Если сервер не ответит в течение этого времени, запрос будет прерван.
- **💤 Таймаут между запросами** – Задержка между запросами в секундах. Полезно для контроля нагрузки на сервер.
- **👞 Шаг таймаута между запросами** – Каждые N запросов будет выполняться дополнительная пауза в N секунд для предотвращения перегрузки.

---

## Рекомендации по настройке

- **Настройка интервала регенерации**: выбирайте интервал в зависимости от частоты обновления контента на сайте. Для новостных порталов подойдёт интервал 60 минут и выше, а для статических сайтов можно выбрать большее значение.
- **Количество запросов за цикл**: оптимально выбирать в зависимости от производительности сервера. Для высоконагруженных сайтов рекомендуется начинать с 50 запросов за минуту.
- **Таймауты**: рекомендуется ставить таймаут запроса не более 5-10 секунд, чтобы избежать долгого ожидания при зависших запросах.
- **Агенты должны работать на кроне**: убедитесь, что агенты работают на кроне

---

## Советы по использованию

- Регулярно проверяйте статус выполнения запросов на странице **"Страницы регенерации"**.
- При возникновении ошибок анализируйте коды ответов и сообщения об ошибках.
- Оптимизируйте параметры таймаутов и количество запросов для минимизации нагрузки на сервер.

---

## Часто задаваемые вопросы (FAQ)

### Вопрос: Что делать, если страницы не регенерируются?
- Убедитесь, что **Агент создан: да**.
- Проверьте корректность ссылок на sitemap.
- Убедитесь, что включена опция **"✅ Включить для сайта"**.

### Вопрос: Почему некоторые страницы не обновляются?
- Возможно, указанные ссылки на sitemap ведут на статичные страницы, которые не изменяются.
- Проверьте статус и коды ответов на странице **"Страницы регенерации"**.

---

## Заключение

Модуль регенерации кэша – это удобный инструмент для автоматизации обновления кэша на сайте. Благодаря гибким настройкам и возможности мониторинга он позволяет эффективно управлять кэшированием и поддерживать высокую скорость загрузки страниц.

Если у вас возникнут вопросы по настройке или работе модуля, обратитесь в службу поддержки [claramente.ru](claramente.ru) или ознакомьтесь с документацией на странице настроек.