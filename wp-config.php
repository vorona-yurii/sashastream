<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе
 * установки. Необязательно использовать веб-интерфейс, можно
 * скопировать файл в "wp-config.php" и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define('DB_NAME', 'sashastream');

/** Имя пользователя MySQL */
define('DB_USER', 'root');

/** Пароль к базе данных MySQL */
define('DB_PASSWORD', '');

/** Имя сервера MySQL */
define('DB_HOST', 'localhost');

/** Кодировка базы данных для создания таблиц. */
define('DB_CHARSET', 'utf8mb4');

/** Схема сопоставления. Не меняйте, если не уверены. */
define('DB_COLLATE', '');

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу.
 * Можно сгенерировать их с помощью {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными. Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'LqQBA*LVfjX#:I&2Fv#BYDOTuG PZh=^05K-1I53;IHU/In-~rwd{1YY;P]527~+');
define('SECURE_AUTH_KEY',  ';2.`;c]-:X/Sz{e:SPlY*FFRFNfKAP&#aVQ+hh$x]+7f~7ID)2kkT[%IiV# e~EB');
define('LOGGED_IN_KEY',    '|2V`~liy^37JozDW`nx_gwc:Cre#S8w^HM|eX:qnR#^7b$4Qk]erg8p+)t54!niT');
define('NONCE_KEY',        'j]A.MhVw|N{aX.[Cs/(ns~x,v$S|6u1wfZz a-aecG&P/(UzIu rvZ}U6SyBNIuc');
define('AUTH_SALT',        'mVp0YG(_Jp,jKQyi-eC4-><w=NuTwykzRIt*1v7HBqKygiAlZqdX*^ cKi$md^Vt');
define('SECURE_AUTH_SALT', ' I hb~^r#]88wDYT<rW+l-1>Pfu!=-W~DCdqMlLpC]!XsXMqGsxm${+)9&lyvRaS');
define('LOGGED_IN_SALT',   '^{DSZ^c;Fg`$@BgSa{xDvi>q/dQaTyB,6r/YTm>` >o,pdQ!vX 3/-^wT3w5crb}');
define('NONCE_SALT',       '{do,|eE@L<HELyhD#2xX=0 w9vvh+BuF98KLz08 {G._c;A)wyni51K37$KzQkV>');

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix  = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 *
 * Информацию о других отладочных константах можно найти в Кодексе.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Инициализирует переменные WordPress и подключает файлы. */
require_once(ABSPATH . 'wp-settings.php');
