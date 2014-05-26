<?php

class YandexIsland {

    /** @var DOMDocument объект документа */
    private $dom;

    /** @var DOMNode фильтры */
    private $filters;

    /**
     * Конструктор
     */
    public function __construct() {
        $this->init();
    }

    /**
     *  Инициализация
     */
    private function init() {

        $this->dom = new DOMDocument('1.0', 'utf-8');
        $site = $this->dom->createElement('site');
        $site->setAttribute('xmlns', 'http://interactive-answers.webmaster.yandex.ru/schemas/site/0.0.1');
        $site->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $site->setAttribute('xsi:schemaLocation', 'http://interactive-answers.webmaster.yandex.ru/schemas/site/0.0.1 / http://interactive-answers.webmaster.yandex.ru/schemas/site-0.0.1.xsd');
        $this->dom->appendChild($site);

        $this->filters = $this->addElementToRoot('filters');
    }

    /**
     * Экспорт xml
     * @param $filename string Имя файла
     */
    public function export($filename){
        $this->dom->save($filename);
    }

    /**
     * Добавление элемента в корень документа
     * @param $name string Имя элемента
     * @param $value string Значение
     * @return DOMElement
     */
    private function addElementToRoot($name, $value = null){

        $element = $this->dom->createElement($name, $value);
        $this->dom->documentElement->appendChild($element);

        return $element;
    }

    /**
     * Установка корневого URL
     * @param $rootUrl string URL сайта
     * @return $this
     */
    public function setRootUrl($rootUrl) {
        $this->addElementToRoot('rootUrl', $rootUrl);
        return $this;
    }

    /**
     * Установка заголовка формы
     * @param $title string Заголовок формы
     * @return $this
     */
    public function setTitle($title) {
        $this->addElementToRoot('title', $title);
        return $this;
    }

    /**
     * Установка протокола
     * @param $protocol string Протокол
     * @return $this
     */
    public function setProtocol($protocol) {
        $this->addElementToRoot('protocol', $protocol);
        return $this;
    }

    /**
     * Установка описания формы
     * @param $description string Описание формы
     * @return $this
     */
    public function setDescription($description) {
        $this->addElementToRoot('description', $description);
        return $this;
    }

    /**
     * Установка id счетчика метрики
     * @param $metricaCounterId integer Идентификатор счетчика метрики
     * @return $this
     */
    public function setMetricaCounterId($metricaCounterId) {
        $this->addElementToRoot('metricaCounterId', $metricaCounterId);
        return $this;
    }

    /**
     * Установка метода для отправки формы
     * @param $method string Метод отправки формы GET или POST
     * @return $this
     */
    public function setRequestMethod($method){
        $this->addElementToRoot('requestMethod', $method);
        return $this;
    }

    /**
     * Устанавливает путь для отправки формы
     * @param $submitUrl string Путь для отправки формы с доменом без протокола
     * @param bool $addSlash Добавить слэш к конец пути
     */
    public function setSubmitUrl($submitUrl, $addSlash = true) {

        $resource = $this->addElementToRoot('resource');

        $parts = explode('/', $submitUrl);
        $elements = array();

        //Добавление слэша в конец, чтобы избежать редиректа и потери поста
        if ($addSlash) {
            $parts[count($parts) - 1] .= '/';
        }

        foreach ($parts as $part) {

            if (!empty($part)) {

                $partElement = $this->dom->createElement('fixed');
                $partElement->setAttribute('name', $part);
                $elements[] = $partElement;
            }
        }

        $elements = array_reverse($elements);

        for ($i = 0; $i < count($elements); $i++) {

            $currentElement = $elements[$i];

            if (isset($elements[$i + 1])) {

                /** @var DOMNode $nextElement */
                $nextElement = $elements[$i + 1];
                $nextElement->appendChild($currentElement);
            }
        }

        $resource->appendChild($elements[count($elements) - 1]);
    }

    /**
     * Добавляет выпадающий список
     *
     * @param $caption string Заголовок поля
     * @param $paramName string Имя параметра
     * @param array $values Элементы выпадающего спискча
     * @param mixed $default Значение по умолчанию
     * @param bool $required Флаг обязательного поля
     */
    public function addDropDown($caption, $paramName, $values = array(), $default = false, $required = true) {

        $filter = $this->dom->createElement('dropDown');

        //Добавляем значение по умолчанию и валидацию
        if ($default) $filter->setAttribute('default', $default);
        if ($required) $filter->setAttribute('required', 'true');

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);
        $setParameter = $this->dom->createElement('setParameter');
        $setParameter->setAttribute('name', $paramName);
        $description->appendChild($setParameter);
        $filter->appendChild($description);

        if ($values) {

            foreach ($values as $value) {

                $valueName = 'dropDownValue';
                $valueElement = $this->dom->createElement($valueName);
                $valueElement->setAttribute('key', $value['key']);
                $valueElement->setAttribute('caption', $value['caption']);
                if (isset($value['default'])) $valueElement->setAttribute('default', 'true');

                $filter->appendChild($valueElement);
            }
        }

        $this->filters->appendChild($filter);
    }

    /**
     * Добавляет галочку
     *
     * @param $caption string Заголовок поля
     * @param $paramName string Имя параметра
     * @param $key string Передаваемое значение
     * @param mixed $default Значение по умолчанию
     * @param bool $required Флаг обязательного поля
     */
    public function addCheckBox($caption, $paramName, $key, $default = false, $required = true){

        $filter = $this->dom->createElement('checkBox');

        if ($required) $filter->setAttribute('required', 'true');

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);
        $setParameter = $this->dom->createElement('setParameter');
        $setParameter->setAttribute('name', $paramName);
        $description->appendChild($setParameter);
        $filter->appendChild($description);

        $checked = $this->dom->createElement('checked');
        $checked->setAttribute('key', $key);
        if ($default) $checked->setAttribute('default', 'true');
        $filter->appendChild($checked);

        $this->filters->appendChild($filter);
    }

    /**
     * Добавляет фильтр максимального и минимального значения
     *
     * @param $caption string Общий заголовок
     * @param $min int От
     * @param $max int До
     * @param $captionFrom string Заголовок От
     * @param $captionTo string Заголовок До
     * @param $nameMin string Имя параметра
     * @param $nameMax string Имя параметра
     * @param mixed $step Шаг
     * @param mixed $unit Единица измерения
     */
    public function addRange($caption, $min, $max, $captionFrom, $captionTo, $nameMin, $nameMax, $step = false,
                             $unit = false) {

        $filter = $this->dom->createElement('rangeFilter');
        $filter->setAttribute('min', $min);
        $filter->setAttribute('max', $max);
        $filter->setAttribute('captionFrom', $captionFrom);
        $filter->setAttribute('captionTo', $captionTo);

        if ($step) $filter->setAttribute('step', $step);
        if ($unit) $filter->setAttribute('unit', $unit);

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);

        $minPriceParam = $this->dom->createElement('setParameter');
        $minPriceParam->setAttribute('name', $nameMin);
        $description->appendChild($minPriceParam);

        $maxPriceParam = $this->dom->createElement('setParameter');
        $maxPriceParam->setAttribute('name', $nameMax);
        $description->appendChild($maxPriceParam);

        $filter->appendChild($description);

        $this->filters->appendChild($filter);
    }

    /**
     * Добавляет текстовое поле
     *
     * @param $caption string Заголовок поля
     * @param $paramName string Имя параметра
     * @param string $type Тип поля, варианты в документации
     * @param mixed $maxChars Максимальное количество символов
     * @param bool $required Флаг обязательного поля
     */
    public function addTextBox($caption, $paramName, $type, $maxChars = false, $required = false) {

        $filter = $this->dom->createElement('textBox');
        if ($type) $filter->setAttribute('type', $type);
        if ($maxChars) $filter->setAttribute('max-chars', $maxChars);
        if ($required) $filter->setAttribute('required', 'true');

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);

        $parameter = $this->dom->createElement('setParameter');
        $parameter->setAttribute('name', $paramName);
        $description->appendChild($parameter);

        $filter->appendChild($description);

        $this->filters->appendChild($filter);
    }

    /**
     * Добавляет фильтр местоположения
     *
     * @param $caption string Заголовок поля
     * @param $paramName string Имя параметра
     * @param bool $useUserRegion Использовать регион пользователя
     * @param bool $isHidden Скрыть поле
     * @param mixed $default По умолчанию
     * @param array $values Список значений
     */
    public function addGeo($caption, $paramName, $useUserRegion = true, $isHidden = true, $default = false,
                                  $values = array()) {

        $filter = $this->dom->createElement('geo');
        $filter->setAttribute('useUserRegion', ($useUserRegion) ? 'true' : 'false');
        $filter->setAttribute('isHidden', ($isHidden) ? 'true' : 'false');
        if ($default) $filter->setAttribute('default', $default);

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);
        $parameter = $this->dom->createElement('setParameter');
        $parameter->setAttribute('name', $paramName);
        $description->appendChild($parameter);
        $filter->appendChild($description);

        if ($values) {

            foreach ($values as $value) {

                $valueName = 'geoValue';
                $valueElement = $this->dom->createElement($valueName);
                $valueElement->setAttribute('key', $value['key']);
                $valueElement->setAttribute('caption', $value['caption']);

                $filter->appendChild($valueElement);
            }
        }

        $this->filters->appendChild($filter);
    }

    /**
     * Добавление маршрута
     *
     * @param $caption string Заголовок маршрута
     * @param $nameFrom string Имя параметра откуда
     * @param $nameTo string Имя параметра куда
     * @param string $useUserRegion Использовать местоположение пользователяя по
     * @param bool $defaultFrom
     * @param bool $defaultTo
     * @param bool $captionFrom
     * @param bool $captionTo
     * @param array $values
     */
    public function addRangeGeo($caption, $nameFrom, $nameTo, $useUserRegion = 'from', $defaultFrom = false,
                                $defaultTo = false, $captionFrom = false, $captionTo = false, $values = array()) {

        $filter = $this->dom->createElement('rangeGeo');
        if ($useUserRegion) $filter->setAttribute('useUserRegion', $useUserRegion);
        if ($defaultFrom) $filter->setAttribute('defaultFrom', $defaultFrom);
        if ($defaultTo) $filter->setAttribute('defaultTo', $defaultTo);
        $filter->setAttribute('captionFrom', $captionFrom);
        $filter->setAttribute('captionTo', $captionTo);

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);
        $parameter = $this->dom->createElement('setParameter');
        $parameter->setAttribute('name', $nameFrom);
        $description->appendChild($parameter);
        $parameter = $this->dom->createElement('setParameter');
        $parameter->setAttribute('name', $nameTo);
        $description->appendChild($parameter);
        $filter->appendChild($description);

        if ($values) {

            foreach ($values as $value) {

                $valueName = 'geoValue';
                $valueElement = $this->dom->createElement($valueName);
                $valueElement->setAttribute('key', $value['key']);
                $valueElement->setAttribute('caption', $value['caption']);

                $filter->appendChild($valueElement);
            }
        }

        $this->filters->appendChild($filter);
    }

    /**
     * Добавляет дату
     *
     * @param $caption string Заголовок поля
     * @param $paramName string Имя параметра
     * @param $format string Форма даты
     * @param mixed $default Значение по умолчанию
     */
    public function addDate($caption, $paramName, $format, $default = false) {

        $filter = $this->dom->createElement('date');
        $filter->setAttribute('format', $format);
        if ($default) $filter->setAttribute('default', $default);

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);
        $parameter = $this->dom->createElement('setParameter');
        $parameter->setAttribute('name', $paramName);
        $description->appendChild($parameter);
        $filter->appendChild($description);

        $this->filters->appendChild($filter);
    }

    /**
     * Добавляет диапазон дат
     *
     * @param $caption string Заголовок поля
     * @param $nameFrom string Имя параметра от
     * @param $nameTo string Имя параметра до
     * @param $format string Формат даты
     * @param $captionFrom string Заголовок поля от
     * @param $captionTo string Заголовок поля до
     * @param mixed $defaultFrom Значение по умолчанию от
     * @param mixed $defaultTo Значение по умолчанию до
     * @param bool $required
     */
    public function addRangeDate($caption, $nameFrom, $nameTo, $format, $captionFrom, $captionTo,
                                 $defaultFrom = false, $defaultTo = false, $required = false) {

        $filter = $this->dom->createElement('rangeDate');
        $filter->setAttribute('format', $format);
        $filter->setAttribute('captionFrom', $captionFrom);
        $filter->setAttribute('captionTo', $captionTo);
        if ($required) $filter->setAttribute('required', 'true');
        if ($defaultFrom) $filter->setAttribute('defaultFrom', $defaultFrom);
        if ($defaultTo) $filter->setAttribute('defaultTo', $defaultTo);

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);
        $paramFrom = $this->dom->createElement('setParameter');
        $paramFrom->setAttribute('name', $nameFrom);
        $description->appendChild($paramFrom);
        $paramTo = $this->dom->createElement('setParameter');
        $paramTo->setAttribute('name', $nameTo);
        $description->appendChild($paramTo);
        $filter->appendChild($description);

        $this->filters->appendChild($filter);
    }

    /**
     * Добавление постоянного параметра
     * Аналог html типа hidden
     *
     * @param $caption string Заголовок
     * @param $paramName string Имя параметра
     * @param $value string Значение
     */
    public function addConstant($caption, $paramName, $value){

        $filter = $this->dom->createElement('constant');
        $filter->setAttribute('key', $value);

        $description = $this->dom->createElement('description');
        $description->setAttribute('caption', $caption);
        $parameter = $this->dom->createElement('setParameter');
        $parameter->setAttribute('name', $paramName);
        $description->appendChild($parameter);
        $filter->appendChild($description);

        $this->filters->appendChild($filter);
    }
}