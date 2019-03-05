<h1>Build #1549541800</h1>
<h2>Date: 2019-03-05</h2>
<div class="changelog">
    - Add CMS main menu items.
</div>
<?php
TCMSLogChange::requireBundleUpdates('ChameleonSystemCoreBundle', 1549541799);

// Define menu items in human-readably format (first column is c = custom menu item, t = table menu item, m = module menu item),

use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\FieldTranslationUtil;

$menuItemDef = <<<EOT
t pkg_shop_primary_navi                 contents
t pkg_cms_text_block                    contents
t pkg_multi_module_set                  contents
t pkg_comment                           contents
t pkg_comment_type                      contents
t data_contact_topic                    contents
t shop_article                          products
t shop_category                         products
t shop_attribute                        products
t shop_article_marker                   products
t shop_article_type                     products
t shop_article_group                    products
t shop_variant_set                      products
t shop_manufacturer                     products
t pkg_shop_article_preorder             products
t shop_vat                              products
t shop_unit_of_measurement              products
t shop_contributor                      products
t shop_contributor_type                 products
t shop_article_document_type            products
t pkg_shop_listfilter                   productlists
t shop_module_articlelist_orderby       productlists
t shop_module_article_list_filter       productlists
t pkg_shop_listfilter_item_type         productlists
t shop_voucher_series                   discounts
t shop_voucher_series_sponsor           discounts
t shop_discount                         discounts
t shop_order                            orders
t shop_order_basket                     orders
t pkg_shop_payment_transaction_type     orders
t shop_order_status_code                orders
t shop_order_step                       checkout
t shop_wrapping                         checkout
t shop_wrapping_card                    checkout
t shop_payment_handler_group            checkout
t shop_shipping_type                    checkout
t shop_shipping_group                   checkout
t shop_shipping_group_handler           checkout
t pkg_shop_currency                     internationalization
t pkg_shop_rating_service_rating        ratings
t pkg_shop_rating_service_history       ratings
t shop_article_review                   ratings
t pkg_shop_rating_service_widget_config ratings
t pkg_shop_rating_service               ratings
t shop_search_cloud_word                search
m articlesearchindex                    search
t shop_search_indexer                   search
t shop_search_query                     search
m shopstats                             analytics
t pkg_external_tracker                  analytics
t pkg_cms_changelog_set                 analytics
t pkg_shop_statistic_group              analytics
t shop_order_export_log                 logs
t shop_search_log                       logs
t pkg_shop_payment_ipn_message          logs
t pkg_shop_payment_ipn_message_trigger  logs
t shop                                  system
m Interface                             dataexchange
t cms_interface_manager                 dataexchange
t pkg_csv2sql                           dataexchange
t shop_variant_display_handler          layout
t shop_variant_type_handler             layout
EOT;

$customMenuItemIconFontCssClasses = [
    'Documents' => 'fas fa-file-alt',
    'Media' => 'far fa-image',
    'Navigation' => 'fas fa-leaf',
];

$databaseConnection = TCMSLogChange::getDatabaseConnection();

// Get data of all tables.

$statement = $databaseConnection->executeQuery('SELECT * FROM `cms_tbl_conf`');
if (false === $statement) {
    TCMSLogChange::addInfoMessage('Could not retrieve list of tables.', TCMSLogChange::INFO_MESSAGE_LEVEL_ERROR);

    return;
}

$tableList = [];
while (false !== $row = $statement->fetch()) {
    $tableList[$row['name']] = $row;
}
$statement->closeCursor();

// Get data of all backend modules.

$statement = $databaseConnection->executeQuery('SELECT * FROM `cms_module`');
if (false === $statement) {
    TCMSLogChange::addInfoMessage('Could not retrieve list of modules.', TCMSLogChange::INFO_MESSAGE_LEVEL_ERROR);

    return;
}

$moduleList = [];
while (false !== $row = $statement->fetch()) {
    $moduleList[$row['uniquecmsname']] = $row;
}
$statement->closeCursor();

// Get data of all custom menu items.

$statement = $databaseConnection->executeQuery('SELECT * FROM `cms_menu_custom_item`');
if (false === $statement) {
    TCMSLogChange::addInfoMessage('Could not retrieve list of custom main menu items.', TCMSLogChange::INFO_MESSAGE_LEVEL_ERROR);

    return;
}

/**
 * @var FieldTranslationUtil $fieldTranslationUtil
 */
$fieldTranslationUtil = ServiceLocator::get('chameleon_system_core.util.field_translation');
/**
 * @var LanguageServiceInterface $languageService
 */
$languageService = ServiceLocator::get('chameleon_system_core.language_service');
$nameFieldNameEn = $fieldTranslationUtil->getTranslatedFieldName(
        'main_menu_custom_item',
        'name',
        $languageService->getLanguageFromIsoCode('en')
);
$customMenuItemList = [];
while (false !== $row = $statement->fetch()) {
    $customMenuItemList[$row[$nameFieldNameEn]] = $row;
}
$statement->closeCursor();

// Get all supported languages.

$primaryLanguage = $databaseConnection->fetchColumn('SELECT `translation_base_language_id` FROM `cms_config`');

$languages = [];
$query = 'SELECT `iso_6391`
          FROM `cms_language` AS l
          JOIN `cms_config_cms_language_mlt` AS mlt ON l.`id` = mlt.`target_id`
          WHERE l.`id` <> ?';
$statement = $databaseConnection->executeQuery($query, [$primaryLanguage]);
if (false === $statement) {
    TCMSLogChange::addInfoMessage('Could not retrieve list of languages.', TCMSLogChange::INFO_MESSAGE_LEVEL_ERROR);

    return;
}

$languageList = [];
while (false !== $row = $statement->fetch()) {
    $languageList[] = $row['iso_6391'];
}
$statement->closeCursor();

// Get main menu category data

$statement = $databaseConnection->executeQuery('SELECT `id`, `system_name` FROM `cms_menu_category`');
if (false === $statement) {
    TCMSLogChange::addInfoMessage('Could not retrieve list of main menu categories.', TCMSLogChange::INFO_MESSAGE_LEVEL_ERROR);

    return;
}

$categoryList = [];
while (false !== $row = $statement->fetch()) {
    $categoryList[$row['system_name']] = $row;
}
$statement->closeCursor();
unset($statement);

// Create menu items.

$menuItemLines = \explode(PHP_EOL, $menuItemDef);
$lastCategory = null;
$invalidTableNames = [];
$invalidModuleNames = [];
$invalidCustomMenuItemNames = [];

foreach ($menuItemLines as $menuItemLine) {
    [$type, $identifier, $category] = \preg_split('#\s+#', $menuItemLine);
    if ($category !== $lastCategory) {
        $position = 0;
        $lastCategory = $category;
    }
    $menuItemId = TCMSLogChange::createUnusedRecordId('cms_menu_item');
    switch ($type) {
        case 't':
            if (false === \array_key_exists($identifier, $tableList)) {
                $invalidTableNames[] = $identifier;
                continue 2;
            }
            $tableData = $tableList[$identifier];
            $menuItemData = [
                'id' => $menuItemId,
                'name' => $tableData['translation'],
                'target' => $tableData['id'],
                'target_table_name' => 'cms_tbl_conf',
                'icon_font_css_class' => $tableData['icon_font_css_class'],
                'position' => $position,
                'cms_menu_category_id' => $categoryList[$category]['id'],
            ];
            foreach ($languageList as $language) {
                if (true === \array_key_exists("translation__$language", $tableData)) {
                    $menuItemData["name__$language"] = $tableData["translation__$language"];
                }
            }
            break;
        case 'm':
            if (false === \array_key_exists($identifier, $moduleList)) {
                $invalidModuleNames[] = $identifier;
                continue 2;
            }
            $moduleData = $moduleList[$identifier];
            $menuItemData = [
                'id' => $menuItemId,
                'name' => $moduleData['name'],
                'target' => $moduleData['id'],
                'target_table_name' => 'cms_module',
                'icon_font_css_class' => $moduleData['icon_font_css_class'],
                'position' => $position,
                'cms_menu_category_id' => $categoryList[$category]['id'],
            ];
            foreach ($languageList as $language) {
                if (true === \array_key_exists("name__$language", $moduleData)) {
                    $menuItemData["name__$language"] = $moduleData["name__$language"];
                }
            }
            break;
        case 'c':
            if (false === \array_key_exists($identifier, $customMenuItemList)) {
                $invalidCustomMenuItemNames[] = $identifier;
                continue 2;
            }
            $customMenuItemData = $customMenuItemList[$identifier];
            $menuItemData = [
                'id' => $menuItemId,
                'name' => $customMenuItemData['name'],
                'target' => $customMenuItemData['id'],
                'target_table_name' => 'cms_menu_custom_item',
                'icon_font_css_class' => $customMenuItemIconFontCssClasses[$customMenuItemData['name']],
                'position' => $position,
                'cms_menu_category_id' => $categoryList[$category]['id'],
            ];

            foreach ($languageList as $language) {
                if (true === \array_key_exists("name__$language", $customMenuItemData)) {
                    $menuItemData["name__$language"] = $customMenuItemData["name__$language"];
                }
            }
            break;
    }

    $databaseConnection->insert('cms_menu_item', $menuItemData);

    ++$position;
}

if (\count($invalidTableNames)) {
    $tableNameString = \implode(', ', $invalidTableNames);
    TCMSLogChange::addInfoMessage(
            "While creating menu items, some tables could not be found. No menu items were created for these tables: $tableNameString",
            TCMSLogChange::INFO_MESSAGE_LEVEL_WARNING
    );
}

if (\count($invalidModuleNames)) {
    $moduleNameString = \implode(', ', $invalidModuleNames);
    TCMSLogChange::addInfoMessage(
        "While creating menu items, some backend modules could not be found. No menu items were created for these modules: $moduleNameString",
        TCMSLogChange::INFO_MESSAGE_LEVEL_WARNING
    );
}

if (\count($invalidCustomMenuItemNames)) {
    $customMenuItemNameString = \implode(', ', $invalidCustomMenuItemNames);
    TCMSLogChange::addInfoMessage(
        "While creating menu items, some custom menu items could not be found. No menu items were created for these custom menu items: $customMenuItemNameString",
        TCMSLogChange::INFO_MESSAGE_LEVEL_WARNING
    );
}
