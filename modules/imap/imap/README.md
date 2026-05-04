# IMAP для Zabbix 5.4

Интерактивная карта хостов для Zabbix 5.4. Модуль показывает хосты по координатам из инвентаря, связи между хостами и актуальные проблемы High/Disaster. По умолчанию используется OpenStreetMap.

## Установка

1. Распакуйте архив в каталог модулей Zabbix:

```bash
/usr/share/zabbix/modules/imap
```

2. В интерфейсе Zabbix откройте `Administration -> General -> Modules`.
3. Нажмите `Scan directory`.
4. Включите модуль `imap`.
5. Откройте карту:

```text
/zabbix/zabbix.php?action=imap.view
```

Если браузер показывает старую версию скриптов или стилей, обновите страницу через `Ctrl+F5`.

## Координаты хостов

Карта берет координаты из инвентаря хоста:

```text
location_lat
location_lon
```

Хост без координат не будет отображаться на карте.

## Иконки оборудования

Иконки лежат здесь:

```text
modules/imap/imap/hardware/
```

Имя PNG-файла должно совпадать со значением поля инвентаря `type`.

Пример:

```text
inventory.type = switch
modules/imap/imap/hardware/switch.png
```

Чтобы заменить иконку, замените соответствующий PNG-файл в `modules/imap/imap/hardware/`. Чтобы добавить новый тип оборудования, добавьте новый PNG-файл и укажите такое же значение в `inventory.type`.

Поле инвентаря, из которого берется тип оборудования, задается в:

```text
modules/imap/legacy/map.view.inc.php
```

Параметр:

```javascript
_imap.settings.hardwareField = 'type';
```

## Дефолтное положение карты

Начальные координаты и zoom задаются в:

```text
modules/imap/legacy/map.view.inc.php
```

Параметры:

```javascript
_imap.settings.startCoordinates = [54.8720, 69.1450];
_imap.settings.startZoom = 13;
```

Формат координат:

```text
[latitude, longitude]
```

Если нужно поменять город по умолчанию, замените `startCoordinates` и при необходимости `startZoom`.

Шаблонные значения также есть в:

```text
modules/imap/imap/settings.js.template
```

В рабочем модуле Zabbix 5.4 основное место правки - `modules/imap/legacy/map.view.inc.php`.

## Слой карты по умолчанию

OpenStreetMap включен как базовый слой по умолчанию в:

```text
modules/imap/legacy/map.view.inc.php
```

Параметры:

```javascript
_imap.settings.defaultBaseLayer = "OpenStreetMap";
_imap.settings.startBaseLayer = "OpenStreetMap";
```

## Триггеры и статусы

Статус маркера рассчитывается по актуальным проблемам Zabbix:

- High - красный маркер;
- Disaster - темно-красный маркер;
- без High/Disaster проблем - зеленый маркер.

Модуль использует текущие Problems и дополнительную фильтрацию триггеров, чтобы не показывать старые закрытые события.

## Links

Связи между хостами хранятся в таблице модуля и отображаются линиями на карте. Для работы связей база Zabbix должна содержать таблицы модуля из SQL-файлов:

```text
modules/imap/imap/tables-mysql.sql
modules/imap/imap/tables-postgresql.sql
```

---

# IMAP for Zabbix 5.4

Interactive host map for Zabbix 5.4. The module displays hosts using inventory coordinates, host links, and current High/Disaster problems. OpenStreetMap is used by default.

## Installation

1. Extract the archive into the Zabbix modules directory:

```bash
/usr/share/zabbix/modules/imap
```

2. In the Zabbix UI, open `Administration -> General -> Modules`.
3. Click `Scan directory`.
4. Enable the `imap` module.
5. Open the map:

```text
/zabbix/zabbix.php?action=imap.view
```

If the browser still shows old scripts or styles, refresh the page with `Ctrl+F5`.

## Host Coordinates

The map reads host coordinates from host inventory:

```text
location_lat
location_lon
```

A host without coordinates will not be displayed on the map.

## Hardware Icons

Hardware icons are stored in:

```text
modules/imap/imap/hardware/
```

The PNG file name must match the host inventory `type` value.

Example:

```text
inventory.type = switch
modules/imap/imap/hardware/switch.png
```

To replace an icon, replace the matching PNG file in `modules/imap/imap/hardware/`. To add a new hardware type, add a new PNG file and use the same value in `inventory.type`.

The inventory field used for hardware type is configured in:

```text
modules/imap/legacy/map.view.inc.php
```

Setting:

```javascript
_imap.settings.hardwareField = 'type';
```

## Default Map Position

Initial map coordinates and zoom are configured in:

```text
modules/imap/legacy/map.view.inc.php
```

Settings:

```javascript
_imap.settings.startCoordinates = [54.8720, 69.1450];
_imap.settings.startZoom = 13;
```

Coordinate format:

```text
[latitude, longitude]
```

To change the default city, update `startCoordinates` and, if needed, `startZoom`.

Template defaults are also available in:

```text
modules/imap/imap/settings.js.template
```

For the Zabbix 5.4 module, the main file to edit is `modules/imap/legacy/map.view.inc.php`.

## Default Map Layer

OpenStreetMap is configured as the default base layer in:

```text
modules/imap/legacy/map.view.inc.php
```

Settings:

```javascript
_imap.settings.defaultBaseLayer = "OpenStreetMap";
_imap.settings.startBaseLayer = "OpenStreetMap";
```

## Triggers And Statuses

Marker status is based on current Zabbix problems:

- High - red marker;
- Disaster - dark red marker;
- no High/Disaster problems - green marker.

The module uses current Problems and additional trigger filtering to avoid showing old resolved events.

## Links

Host links are stored in the module database table and displayed as lines on the map. The Zabbix database must contain module tables from the SQL files:

```text
modules/imap/imap/tables-mysql.sql
modules/imap/imap/tables-postgresql.sql
```
