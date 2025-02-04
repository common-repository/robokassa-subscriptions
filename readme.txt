=== Robokassa payment gateway with Subscriptions support ===
Contributors: Robokassa
Tags: robokassa payment gateway, robokassa, ecommerce, payment gateway, subscriptions, recurring payment, рекуррентные платежи, подписки, рекурренты ===
Requires at least: 5.7
Tested up to: 6.1
Stable tag: 1.1.5
Requires PHP: 5.6.32
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Robokassa сделала свой популярный модуль для WooCommerce еще лучше - теперь он поддерживает не только обычные продажи, но и функционал подписок, а также продажи цифрового контента WooCommerce Subscriptions. При этом вы можете сами выбрать - просто напоминать покупателю об оплате или же подписать его на рекуррентные платежи. В нужное время указанную вами сумму модуль спишет сам. Вам достаточно лишь [приобрести лицензию](https://woocommerce.com/products/woocommerce-subscriptions/) на сам модуль WooCommerce Subscriptions.

== Основные возможности ==

Плагин поддерживает все основные функции для приема платежей:

* Весь функционал формирования системы подписок на товары WooCommerce Subscriptions
* Возможность автоматических списаний
* Передачу состава товаров в заказе для отправки чека клиенту и в налоговую (54-ФЗ). Сама Robokassa при этом выступает Агентом по отправке чеков. Решение [“Робочеки”](https://robokassa.com/robokassa/robocheck/)
* Работу в РФ (рубли) и Казахстане (тенге)
* Автоматическую отправку второго чека после смены статуса заказа
* Передачу маркировки в чек и ОФД
* Выбор платежной системы при оформлении заказа до отправки на страницу оплаты
* Выбор системы налогообложения, всех необходимых параметров чека по 54-ФЗ
* Выбор размера ставки НДС для товаров в заказе
* Выгрузку товарных позиций в YML - например, на площадку Robo.market
* Оплату через iframe (при таком типе оплаты не все платежные системы доступны)

== О Robokassa ==

Robokassa – ведущий сервис по приёму платежей в сети Интернет, более 18 лет предлагающий максимально широкий спектр возможностей для оплаты товаров и услуг. Порядок регистрации в сервисе подробно описан в [документации Robokassa](https://docs.robokassa.ru/#7844).
Основные преимущества:

* Быстрая интеграция, дистанционное заключение договора
* Отсутствие абонентской платы и скрытых комиссий
* Работа в РФ и Казахстане
* Все самые популярные способы оплаты, включая Apple Pay, Google Pay, Samsung Pay и карты рассрочки
* Удобный Личный кабинет
* Комплекс решений по 54-ФЗ
* Поддержка 24/7

== Фискализация ==

Для подключения автоматического формирования чеков в соответствии с 54-ФЗ необходимо подключить одну из доступных фискальных схем в Личном кабинете Robokassa ([Раздел "Фискализация"](https://auth.robokassa.ru/partner/Login.aspx?ReturnUrl=https%3a%2f%2fpartner.robokassa.ru%2fFiscalization)), а также указать настройки модуля:

* Систему налогообложения
* Признак способа расчёта
* Признак предмета расчёта

== Рекуррентные платежи ==

Рекуррентные платежи позволяют вам один раз подписать карту покупателя - после этого Robokassa сама по заданному графику будет списывать деньги, пока клиент не отпишется. Достаточно просто создать товар со свойством “По подписке” - и просто его опубликовать.

ВНИМАНИЕ! Использование функционала периодических списаний возможно только при подключенной функции в Личном кабинете Robokassa. Для этого надо написать запрос, а с ним прислать скриншот страницы подписки, на которой покупатель соглашается с офертой и повторными списаниями - и получает информацию об отписке. Такая страница уже есть в комплекте модуля.

== Поддержка ==

Если у вас есть вопрос или пожелания или не хватает предложенного функционала - напишите нам, мы всегда рады вам помочь. Если вы уже клиент Robokassa - мы ждем ваших запросов из Личного кабинета (раздел “Поддержка”), если еще нет - на почту [support@robokassa.ru](support@robokassa.ru). Пожалуйста, для решения вопросов будьте готовы предоставить доступ к административной панели WordPress и FTP.
Наш сайт: [https://robokassa.com](https://robokassa.com)
Телефон: 8 (800) 500-25-57



== Installation ==

Этапы установки плагина на сайт:

1. Скачайте репозиторий в папку /wp-content/plugins/
2. Активируйте плагин в настройках WordPress /wp-admin/plugins.php
3. Настройте параметры подключения /wp-admin/admin.php?page=robokassa_payment_main_rb

Настройка магазина на стороне [Робокассы](https://auth.robokassa.ru/partner/Login.aspx)

1. Алгоритм расчета хеша – MD5
2. Result Url – http(s)://your-domain.ru/?robokassa=result
3. Success Url – http(s)://your-domain.ru/?robokassa=success
4. Fail Url – http(s)://your-domain.ru/?robokassa=fail
5. Метод отсылки данных по Result Url, Success Url и fail Url  – POST

Настройка на стороне сайта:

1. Указать платежные данные: Логин магазина, Пароль магазина #1, Пароль магазина #2
2. Активировать тестовый режим при необходимости, так же необходимо будет внести: Пароль магазина для тестов #1 и Пароль магазина для тестов #2

== Frequently Asked Questions ==

= Ошибка "Нет доступных методов оплаты"  =

Если при сохранении настроек выдается такая ошибка - это значит наш модуль не может получить доступные способы оплаты для магазина. Проверьте и укажите правильный идентификатор мерчанта и Пароль 1.

= Как настроить фискализацию =

Для корректной передачи данных чека вам нужно настроить параметры - укажите вашу систему налогообложения (УСН, ОСН и т.д.), Признак способа расчета (Аванс, Предоплата, Предоплата 100%, Полная оплата и т.д. ) и Признак предмета расчета (Товар, Услуга и т.д.). Настройки одинаковы - используете ли вы Робочеки или у вас своя касса, подключенная к решениям Кассовое или Облачное в Личном Кабинете Робокассы.

= Как настроить фискализацию =

Второй чек нужно отправлять, если вы в настройках Фискализации указали Способ оплаты Аванс, Предоплата или Предоплата 100%. Наш модуль отправит второй чек сам после того, как вы смените статус заказа на Выполнен. Также, в любой момент отправка второго чека доступна в Личном Кабинете Робокассы, раздел Операции и Возвраты.

= Как отправить маркировку товара =

Наш модуль сам отправит маркировку в чек, просто заполните поле Артикул в карточке товара. Если вы забыли это сделать - то вы можете зайти в Личный Кабинет Робокассы, раздел Операции и Возвраты - и при отправке второго чека добавьте маркировку.

= Не меняется статус заказа =

Если после успешной оплаты у вас не сменился статус заказа - проверьте правильность указания Result URL и Пароль 2.

= Как вывести все платежные системы на странице оформления заказа =

Активировать режим выбора оплаты в магазине
Настройки плагина > Общие настройки > Выбор способа оплаты – В магазине

= Что такое рекуррентные платежи? =

Рекуррентные платежи- это способ автоматического возобновления подписки, после списания денег с карты плательщика. Ему достаточно при первой оплате согласиться с тем, что его карта будет сохранена и он не против повторных оплат. В данном модуле эта функция реализована через модуль WooCommerce Subscriptions. Для того, чтобы отписаться - покупателю нужно будет зайти в созданный ему Личный кабинет, раздел Подписки.

= Почему рекуррентные платежи требуют согласования? =

Все платежи по банковским картам защищены от мошенничества. Покупатель в любой момент может обратиться с претензией - если он не понял, что с него будут проводиться повторные списания-  или он не соглашался. Поэтому в нашем модуле уже сделана готовая форма согласия для покупателя - где он соглашается с офертой, повторными списаниями и получает информацию об отписке. Для согласования включения рекуррентных платежей Робокассой вам достаточно прислать нам ссылку на эту страницу.

== Screenshots ==

1. `/assets/screenshot-1.png`
2. `/assets/screenshot-2.png`
3. `/assets/screenshot-3.png`

== Changelog ==

[Ссылка на GitHub со всеми новостями](https://github.com/robokassa/cms-wordpress-subscriptions)

= 1.0.0 =
* Добавлена поддержка плагина регулярных платежей

= 1.0.1 =
* Добавлена поддержка WordPress 5.8

= 1.0.2 =
* Добавлена проверка факта установки плагина WooCommerce

= 1.1.0 =
* Добавлен функционал регистрации
* Изменен дизайн страницы настроек

= 1.1.5 =

Добавлен параметр cost для расчёта фискализации
Устранены ошибки внутри плагина

= 1.1.6 =

Иправлена ошибка передачи номенклатуры при вариативном товаре
