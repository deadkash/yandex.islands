# Генератор Яндекс.островов

## Использование

```php
$island = new YandexIsland();

$island->setRootUrl('http://www.example.com');
$island->setTitle('Title of island');
$island->setProtocol('HTTP');
$island->setRequestMethod('POST');
$island->setDescription('Island description');
$island->setSubmitUrl('www.example.com/custompage');
$island->setMetricaCounterId(22233344);

$island->addRangeDate('Дата', 'dateFrom', 'dateTo', 'yyyy-MM-dd', 'Заезд', 'Отъезд', false, false, true);
$island->addTextBox('Количество человек', 'count', 'AllUnparsed', 40);
$island->addDropDown('Номер', 'nomer', $values);

$island->export('island.xml');
```
