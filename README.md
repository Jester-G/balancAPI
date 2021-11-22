# API для работы с балансом пользователей.
<h3>Установка</h3>
<ol>
  <li>Скачать архив или клонировать проект через git;</li>
  <li>Создать пустую базу данных и выполнить бэкап с помощью файла db.sql для добавления таблиц и пользователей;</li>
  <li>В файле, по пути config/Database.php, добавить свои данные для соединения с базой данных;</li>
  <li>Запустить в корневой директории php server, используя команду php -S localhost:8080;</li>
  <li>Использовать методы для работы с балансом пользователя.</li>
</ol>
<hr>
<h3>Информация по базе данных</h3>
<p>Для хранения баланса используется тип int. Валюта - RUB, поэтому баланс хранится в копейках ('1000' => 10&#8381;).</p>
<h4>Структура таблиц в БД</h4>

<pre>
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
</pre>

<pre>
CREATE TABLE `balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
</pre>

<pre>
CREATE TABLE `transactions` (
  `id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
</pre>
users - таблица с пользователями; <br>
balance - таблица с балансом пользователей; <br>
transactions - таблица с информацией о транзакциях пользователей; <br>
<hr>
<h3>Доступные методы</h3>

<h4>1. Получение текущего баланса пользователя</h4>
Пример. Получить баланса пользователя с ID = 1 в долларах
<pre>
curl -X GET 'localhost:8080/src/api/balance/singleUser.php?id=1&amp;currency=usd'
</pre>
<ul><b>Параметры запроса</b>
  <li><b>id - обязательный параметр, подставляем ID пользователя</b>;</li>
  <li>currency - необязательный параметр (по умолчанию рубли), если указано USD присходит конвертация баланса пользователя.</li>
</ul>

<h4>2. Начисление/списание средств с баланса пользователя</h4>
Пример. Пополнить баланс пользователя с ID = 1 на 100&#8381 (10000 копеек)
<pre>
curl -X PUT 'localhost:8080/src/api/balance/setBalance.php' \
--header 'Content-Type: application/json' \
--data-raw '{
    "id": 1,
    "amount": 10000
}'
</pre>
Пример. Списать с баланса пользователя с ID = 1 100&#8381 (10000 копеек)
<pre>
curl -X PUT 'localhost:8080/src/api/balance/setBalance.php' \
--header 'Content-Type: application/json' \
--data-raw '{
    "id": 1,
    "amount": -10000
}'
</pre>
<ul><b>Параметры запроса</b>
  <li><b>id - обязательный параметр, подставляем ID пользователя</b>;</li>
  <li><b>amount - обязательный параметр, подставляем сумму зачисления в копейках (в нашем случае "10000" = 100&#8381;)</b>.</li>
</ul>

<h4>3. Перевод средств от пользователя к пользователю</h4>
Пример. Перевести 100&#8381; с баланса пользователя 1 на баланс пользователя 2 
<pre>
curl -X PUT 'localhost:8080/src/api/balance/transfer.php' \
--header 'Content-Type: application/json' \
--data-raw '{
    "from": 1,
    "to" : 2,
    "amount": 10000
    }'
</pre>
<ul><b>Параметры запроса</b>
  <li><b>from - обязательный параметр, подставляем ID пользователя, с которого нужно списать средства</b>;</li>
  <li><b>to - обязательный параметр, подставляем ID пользователя, которому нужно перевести средства</b>;</li>
  <li><b>amount - обязательный параметр, подставляем сумму зачисления в копейках (в нашем случае "10000" = 100&#8381;)</b>.</li>
</ul>

<h4>4. Получение списка транзакций пользователя</h4>
Пример. Получить транзакции пользователя с ID = 1
<pre>
curl -X GET 'localhost:8080/src/api/balance/transactions.php?id=1&amp;page=1&amp;sort=sum&amp;order=asc'
</pre>
<ul><b>Параметры запроса</b>
  <li><b>id - обязательный параметр, подставляем ID пользователя</b>;</li>
  <li>page - необязательный параметр (по умолчанию 1), если указано, возвращает текущую страницу (если такая существует);</li>
  <li>sort - необязательный параметр сортировки (по умолчанию сортирует по дате), если указано sum сортирует по сумме транзакции;</li>
  <li>order - необязательный параметр (по умолчанию сортирует по убыванию), если указано asc сортирует по возрастанию.</li>
</ul>
