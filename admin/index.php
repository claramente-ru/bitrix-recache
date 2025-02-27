<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

/** @global $APPLICATION CMain */
global $APPLICATION;

$APPLICATION->SetTitle('Настройки модуля');
$request = Application::getInstance()->getContext()->getRequest();
$moduleId = 'claramente.recache';

if (! Loader::includeModule('claramente.recache')) {
    throw new Exception('Необходимо установить модуль claramente.recache');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

// Сохранение параметров
if ($request->isPost() && check_bitrix_sessid()) {
    // Сохранение параметров
    if (is_array($request->getPost('options'))) {
        foreach ($request->getPost('options') as $siteId => $options) {
            foreach ((array)$options as $name => $value) {
                Option::set($moduleId, $name, $value, $siteId);
            }
        }
    }

    // Перенаправление для сохранения данных
    LocalRedirect($request->getRequestUri());
}

// Создание агента
if ($request->getQuery('create_agent') === 'Y') {
    ClaramenteRecacheProcessAgent::addAgent();
    CAdminMessage::ShowNote('Агент успешно создан');
}
// Удаление агента
if ($request->getQuery('delete_agent') === 'Y') {
    ClaramenteRecacheProcessAgent::deleteAgent();
    CAdminMessage::ShowMessage('Агент успешно удален');
}

// Список сайтов
$sites = CSite::GetList();

// Агент
$agent = ClaramenteRecacheProcessAgent::getAgent();
$lastAgentRun = $agent && $agent['LAST_EXEC'] ? $agent['LAST_EXEC'] : 'Не запускался';

// Таблица настроек
$tabControl = new CAdminTabControl('tabControl', [
    ['DIV' => 'edit1', 'TAB' => 'Основные настройки', 'TITLE' => 'Настройки модуля'],
    ['DIV' => 'about', 'TAB' => 'ℹ️ О модуле', 'TITLE' => 'ℹ️ О модуле'],
]);
?>

<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>?lang=<?= LANG ?>">
    <?= bitrix_sessid_post() ?>
    <?php $tabControl->Begin(); ?>
    <?php $tabControl->BeginNextTab(); ?>
    <tr class="heading" id="tr_tab-edit-tabs[1]"><td colspan="2">Общие настройки</td></tr>

    <tr>
        <td>Использовать агенты для регенерации:</td>
        <td><input type="checkbox" name="USE_AGENTS" value="Y" checked disabled /></td>
    </tr>

    <tr>
        <td>🛂 Агент создан:</td>
        <td>
            <b><?= $agent ? "Да" : "Нет" ?></b>
            <?php if ($agent) { ?>
                <!-- Кнопка удалить агент -->
                <br/>
                <a href="<?= sprintf('%s?lang=%s&delete_agent=Y', $request->getRequestedPage(), LANG) ?>"><input
                            type="button" value="Удалить агент" class="adm-btn-remove"/></a>
            <?php } ?>
            <?php if (!$agent) { ?>
                <!-- Кнопка создать агент -->
                <br/>
                <a href="<?= sprintf('%s?lang=%s&create_agent=Y', $request->getRequestedPage(), LANG) ?>"><input
                            type="button" value="Создать агент" class="adm-btn-save"/></a>
            <?php } ?>
        </td>
    </tr>

    <tr>
        <td>📅 Последний запуск агента:</td>
        <td><b><?= htmlspecialcharsbx($lastAgentRun) ?></b></td>
    </tr>

    <!-- Информация по каждому сайту -->
    <?php while ($site = $sites->Fetch()) {
        // Последний запуск регенерации
        $lastRecacheExec = Option::get($moduleId, 'recache_exec', 'Не запускалось', $site['SITE_ID']);
        // Включить для сайта
        $enableForSite = Option::get($moduleId, 'recache_enable', 'N', $site['SITE_ID']);
        ?>
        <tr class="heading" id="tr_tab-edit-tabs[1]">
            <td colspan="2">Сайт: <?= $site['SITE_ID'] ?> - <?= htmlspecialcharsbx($site['SERVER_NAME']) ?></td>
        </tr>
        <tr>
            <td>✅ Включить для сайта:</td>
            <td>
                <input type="hidden" name="options[<?= $site['SITE_ID'] ?>][recache_enable]" value="N">
                <input type="checkbox" name="options[<?= $site['SITE_ID'] ?>][recache_enable]"
                       value="Y" <?php if ($enableForSite === 'Y') { ?> checked<?php } ?> />
            </td>
        </tr>

        <tr>
            <td>🔗 Ссылки на sitemap:</td>
            <td>
                <input type="text" size="50" name="options[<?= $site['SITE_ID'] ?>][sitemap_url]" value="<?=
                htmlspecialcharsbx(Option::get($moduleId, 'sitemap_url', '', $site['SITE_ID'])) ?>"/>
                <br>
                <span class="cm-span-comment">Через запятую если несколько ссылок</span>
            </td>
        </tr>

        <tr>
            <td>📅 Последний запуск регенерации:</td>
            <td><b><?= htmlspecialcharsbx($lastRecacheExec) ?></b></td>
        </tr>

        <tr>
            <td>🔁 Интервал принудительного автоматического запуска регенерации:</td>
            <td><input type="text" name="options[<?= $site['SITE_ID'] ?>][recache_start_period]" value="<?=
                (int)Option::get($moduleId, 'recache_start_period', 0, $site['SITE_ID']) ?>"/>
                <span class="cm-span-comment">0 = отключено (минуты)</span>
            </td>
        </tr>

        <tr>
            <td>🚦 Количество запросов для обработки за цикл:</td>
            <td><input type="text" name="options[<?= $site['SITE_ID'] ?>][request_agent_limit]" value="<?=
                (int)Option::get($moduleId, 'request_agent_limit', 100, $site['SITE_ID']) ?>"/></td>
        </tr>

        <tr>
            <td>⏳️ Таймаут запроса:</td>
            <td>
                <input type="text" name="options[<?= $site['SITE_ID'] ?>][request_timeout]" value="<?=
                (int)Option::get($moduleId, 'request_timeout', 3, $site['SITE_ID']) ?>"/>
                <span class="cm-span-comment">(секунды)</span>
            </td>
        </tr>

        <tr>
            <td>💤 Таймаут между запросами:</td>
            <td>
                <input type="text" name="options[<?= $site['SITE_ID'] ?>][request_timeout_between_requests]" value="<?=
                (int)Option::get($moduleId, 'request_timeout_between_requests', 0, $site['SITE_ID']) ?>"/>
                <span class="cm-span-comment">(секунды)</span>
            </td>
        </tr>

        <tr>
            <td>👞 Шаг таймаута между запросами:</td>
            <td><input type="text" name="options[<?= $site['SITE_ID'] ?>][request_timeout_step]" value="<?=
                (int)Option::get($moduleId, 'request_timeout_step', 15, $site['SITE_ID']) ?>"/>
            </td>
        </tr>
    <?php } ?>

    <?php $tabControl->EndTab(); ?>
    <?php $tabControl->BeginNextTab(); ?>

    <tr id="tr_about-license">
        <td width="40%" class="adm-detail-content-cell-l">⚖️ Лицензия</td>
        <td class="adm-detail-content-cell-r"><a target="_blank"
                                                 href="https://github.com/claramente-ru/bitrix-recache/blob/master/LICENSE">MIT</a>
        </td>
    </tr>
    <tr id="tr_about-git">
        <td width="40%" class="adm-detail-content-cell-l">𝗚𝐈𝗧️ GitHub</td>
        <td class="adm-detail-content-cell-r"><a target="_blank" href="https://github.com/claramente-ru/bitrix-recache">https://github.com/claramente-ru/bitrix-recache</a>
        </td>
    </tr>
    <tr id="tr_about-packagist">
        <td width="40%" class="adm-detail-content-cell-l">🐘️ Packagist</td>
        <td class="adm-detail-content-cell-r"><a target="_blank"
                                                 href="https://packagist.org/packages/claramente/claramente.recache">https://packagist.org/packages/claramente/claramente.recache</a>
        </td>
    </tr>
    <tr id="tr_about-developer">
        <td width="40%" class="adm-detail-content-cell-l">⚒️ Разработчик</td>
        <td class="adm-detail-content-cell-r"><a target="_blank" href="https://claramente.ru">© Светлые головы</a></td>
    </tr>

    <?php $tabControl->Buttons(); ?>
    <input type="submit" name="save" value="Сохранить настройки" class="adm-btn-save" />
    <?php $tabControl->End(); ?>
</form>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';