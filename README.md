# Interactive Map for Zabbix 5.4

> Порт старой интерактивной карты `imap.php` под модуль фронтенда Zabbix 5.4.  
> A frontend module port of the old `imap.php` interactive map for Zabbix 5.4.

## Источники / Original Projects

Этот порт основан на старом модуле IMAP для Zabbix и адаптирован под frontend modules Zabbix 5.4.

Original projects:

- [hunter-kaan/imap](https://github.com/hunter-kaan/imap/tree/master)
- [RussianFox/imap](https://github.com/RussianFox/imap)

## Содержание

- [Источники / Original Projects](#источники--original-projects)
- [Русский](#русский)
  - [Возможности](#возможности)
  - [Установка](#установка)
  - [Таблицы базы данных](#таблицы-базы-данных)
  - [Координаты хостов](#координаты-хостов)
  - [Где менять иконки оборудования](#где-менять-иконки-оборудования)
  - [Где менять дефолтное положение карты](#где-менять-дефолтное-положение-карты)
  - [Где менять карту по умолчанию](#где-менять-карту-по-умолчанию)
  - [Статусы маркеров](#статусы-маркеров)
- [English](#english)
  - [Features](#features)
  - [Installation](#installation)
  - [Database Tables](#database-tables)
  - [Host Coordinates](#host-coordinates)
  - [Where to Change Hardware Icons](#where-to-change-hardware-icons)
  - [Where to Change the Default Map Position](#where-to-change-the-default-map-position)
  - [Where to Change the Default Map Layer](#where-to-change-the-default-map-layer)
  - [Marker Statuses](#marker-statuses)

---

## Русский

Интерактивная карта для Zabbix 5.4 показывает хосты по координатам из инвентаря, связи между хостами и текущие проблемы уровня `High` / `Disaster`.

По умолчанию используется `OpenStreetMap`.

### Возможности

- Нативный frontend module для Zabbix 5.4.
- Страница карты: `zabbix.php?action=imap.view`.
- AJAX через `zabbix.php?action=imap.ajax`.
- Хосты отображаются по `location_lat` и `location_lon` из инвентаря.
- Иконки оборудования берутся из поля инвентаря `type`.
- Цвет маркера показывает статус: зеленый, красный, темно-красный.
- Google Maps / StreetView отключены, чтобы не было предупреждения `NoApiKeys`.

### Установка

Скопируйте модуль в каталог frontend modules Zabbix:

```bash
sudo cp -a modules/imap /usr/share/zabbix/modules/imap
```

Если пользователи еще открывают старую ссылку `/zabbix/imap.php`, можно также скопировать файл-редирект:

```bash
sudo cp -a imap.php /usr/share/zabbix/imap.php
```

Дальше в интерфейсе Zabbix:

1. Откройте `Administration -> General -> Modules`.
2. Нажмите `Scan directory`.
3. Включите модуль `Interactive map` / `imap`.
4. Откройте `Monitoring -> Interactive map`.

Прямая ссылка:

```text
/zabbix/zabbix.php?action=imap.view
```

После обновления файлов сделайте `Ctrl+F5`, иначе браузер может оставить старые `build.js` и `markers.css`.

### Таблицы базы данных

Связи между хостами хранятся в таблицах модуля. Если таблицы еще не созданы, импортируйте SQL под вашу БД.

MySQL / MariaDB:

```bash
mysql -u zabbix -p zabbix < /usr/share/zabbix/modules/imap/imap/tables-mysql.sql
```

PostgreSQL:

```bash
sudo -u zabbix psql -U zabbix -W -d zabbix < /usr/share/zabbix/modules/imap/imap/tables-postgresql.sql
```

### Координаты хостов

Карта берет координаты из инвентаря хоста:

| Поле инвентаря | Назначение |
| --- | --- |
| `location_lat` | Широта |
| `location_lon` | Долгота |

Хост без координат на карте не отображается.

### Где менять иконки оборудования

Иконки оборудования лежат в каталоге:

```text
modules/imap/imap/hardware/
```

На сервере Zabbix:

```text
/usr/share/zabbix/modules/imap/imap/hardware/
```

Имя PNG-файла должно совпадать со значением поля инвентаря `type`.

| Значение `inventory.type` | Файл иконки |
| --- | --- |
| `switch` | `hardware/switch.png` |
| `router` | `hardware/router.png` |
| `radio` | `hardware/radio.png` |
| `aggregation` | `hardware/aggregation.png` |

Чтобы заменить иконку, замените соответствующий PNG-файл в `modules/imap/imap/hardware/`.

Чтобы добавить новый тип оборудования:

1. Добавьте PNG-файл, например `modules/imap/imap/hardware/radio_bridge.png`.
2. В инвентаре хоста укажите `type = radio_bridge`.

Поле инвентаря, из которого берется тип оборудования, задается здесь:

```text
modules/imap/legacy/map.view.inc.php
```

Параметр:

```javascript
_imap.settings.hardwareField = 'type';
```

Если нужно брать тип из другого поля инвентаря, замените `'type'` на нужное поле.

### Где менять дефолтное положение карты

Начальный центр карты и масштаб задаются здесь:

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

Для другого города поменяйте `startCoordinates` и при необходимости `startZoom`.

Шаблонные значения также есть здесь:

```text
modules/imap/imap/settings.js.template
```

Для Zabbix 5.4 основное место правки - `modules/imap/legacy/map.view.inc.php`.

### Где менять карту по умолчанию

Базовый слой карты задается здесь:

```text
modules/imap/legacy/map.view.inc.php
```

Параметры:

```javascript
_imap.settings.defaultBaseLayer = 'OpenStreetMap';
_imap.settings.startBaseLayer = 'OpenStreetMap';
```

### Статусы маркеров

| Цвет | Значение |
| --- | --- |
| Зеленый | Нет текущих `High` / `Disaster` проблем |
| Красный | Есть текущая проблема `High` |
| Темно-красный | Есть текущая проблема `Disaster` |

Иконка оборудования рисуется поверх цветной заливки статуса.

---

## English

Interactive Map for Zabbix 5.4 displays hosts using inventory coordinates, host links, and current `High` / `Disaster` problems.

`OpenStreetMap` is used by default.

### Features

- Native frontend module for Zabbix 5.4.
- Map page: `zabbix.php?action=imap.view`.
- AJAX through `zabbix.php?action=imap.ajax`.
- Hosts are displayed using `location_lat` and `location_lon` inventory fields.
- Hardware icons are selected using the inventory `type` field.
- Marker color shows status: green, red, dark red.
- Google Maps / StreetView are disabled to avoid the `NoApiKeys` warning.

### Installation

Copy the module to the Zabbix frontend modules directory:

```bash
sudo cp -a modules/imap /usr/share/zabbix/modules/imap
```

If users still open the old `/zabbix/imap.php` URL, you can also copy the redirect file:

```bash
sudo cp -a imap.php /usr/share/zabbix/imap.php
```

Then in the Zabbix frontend:

1. Open `Administration -> General -> Modules`.
2. Click `Scan directory`.
3. Enable the `Interactive map` / `imap` module.
4. Open `Monitoring -> Interactive map`.

Direct URL:

```text
/zabbix/zabbix.php?action=imap.view
```

After replacing files, refresh the page with `Ctrl+F5`; otherwise the browser may keep old `build.js` and `markers.css`.

### Database Tables

Host links are stored in module database tables. If the tables are not created yet, import the SQL file matching your database.

MySQL / MariaDB:

```bash
mysql -u zabbix -p zabbix < /usr/share/zabbix/modules/imap/imap/tables-mysql.sql
```

PostgreSQL:

```bash
sudo -u zabbix psql -U zabbix -W -d zabbix < /usr/share/zabbix/modules/imap/imap/tables-postgresql.sql
```

### Host Coordinates

The map reads host coordinates from inventory:

| Inventory field | Purpose |
| --- | --- |
| `location_lat` | Latitude |
| `location_lon` | Longitude |

A host without coordinates is not displayed on the map.

### Where to Change Hardware Icons

Hardware icons are stored in:

```text
modules/imap/imap/hardware/
```

On the Zabbix server:

```text
/usr/share/zabbix/modules/imap/imap/hardware/
```

The PNG file name must match the inventory `type` value.

| `inventory.type` value | Icon file |
| --- | --- |
| `switch` | `hardware/switch.png` |
| `router` | `hardware/router.png` |
| `radio` | `hardware/radio.png` |
| `aggregation` | `hardware/aggregation.png` |

To replace an icon, replace the matching PNG file in `modules/imap/imap/hardware/`.

To add a new hardware type:

1. Add a PNG file, for example `modules/imap/imap/hardware/radio_bridge.png`.
2. Set `type = radio_bridge` in host inventory.

The inventory field used for hardware type is configured here:

```text
modules/imap/legacy/map.view.inc.php
```

Setting:

```javascript
_imap.settings.hardwareField = 'type';
```

If you want to read hardware type from another inventory field, replace `'type'` with the required field name.

### Where to Change the Default Map Position

Initial map center and zoom are configured here:

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

To set another default city, change `startCoordinates` and, if needed, `startZoom`.

Template defaults are also available here:

```text
modules/imap/imap/settings.js.template
```

For Zabbix 5.4, the main file to edit is `modules/imap/legacy/map.view.inc.php`.

### Where to Change the Default Map Layer

The default base map is configured here:

```text
modules/imap/legacy/map.view.inc.php
```

Settings:

```javascript
_imap.settings.defaultBaseLayer = 'OpenStreetMap';
_imap.settings.startBaseLayer = 'OpenStreetMap';
```

### Marker Statuses

| Color | Meaning |
| --- | --- |
| Green | No current `High` / `Disaster` problems |
| Red | Current `High` problem exists |
| Dark red | Current `Disaster` problem exists |

The hardware icon is drawn on top of the colored status fill.
