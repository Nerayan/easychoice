<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Parsing and save info by product categories.
 */
class Groups
{
    /**
     * Processing progress data.
     *
     * @var array
     */
    protected static $processData = [];

    /**
     * The flag determines whether the list of groups will be saved to the option or not.
     *
     * @var bool
     */
    public static $saveGroupListToOption = false;

    /**
     * @param \XMLReader $reader
     *
     * @return bool
     */
    public static function process(\XMLReader $reader)
    {
        /*
         * Example xml structure
         * position - Классификатор -> Группы
         *
        <Группы>
            <Группа>
                <Ид>ce075b7b-8800-11e3-9415-d4ae52cbdbf0</Ид>
                <Наименование>Group 1</Наименование>
            </Группа>
            <Группа>
                <Ид>3432b3a9-635f-11e3-940c-d4ae52cbdbf0</Ид>
                <Наименование>Group 2</Наименование>
                <Группы>
                    <Группа>
                        <Ид>fe4e0f61-a7eb-11e4-9445-d4ae52cbdbf0</Ид>
                        <Наименование>Group 2.1</Наименование>
                    </Группа>
                </Группы>
            </Группа>
        </Группы>
        */

        // ignore if empty data
        if (
            $reader->name === 'Группы' &&
            $reader->nodeType === \XMLReader::ELEMENT &&
            str_replace(' ', '', $reader->readOuterXml()) === '<Группы/>'
        ) {
            return true;
        }

        // if start new block, add the current category as the last parent on the stack
        if ($reader->name === 'Группы' && $reader->nodeType === \XMLReader::ELEMENT) {
            if (self::$processData['numberOfCategories'] >= $_SESSION['IMPORT_1C']['numberOfCategories']) {
                array_unshift(self::$processData['categoryIdStack'], self::$processData['currentCategoryId']);
            }
        }

        // if end block, remove the last parent from the stack, as the level of nesting has changed
        // and you need to bind from the previous level
        if ($reader->name === 'Группы' && $reader->nodeType === \XMLReader::END_ELEMENT) {
            if (self::$processData['numberOfCategories'] >= $_SESSION['IMPORT_1C']['numberOfCategories']) {
                array_shift(self::$processData['categoryIdStack']);
            }
        }

        if ($reader->name !== 'Группа' || $reader->nodeType !== \XMLReader::ELEMENT) {
            return true;
        }

        // check time execution limit
        if (!HeartBeat::nextTerm()) {
            return false;
        }

        $element = simplexml_load_string(trim($reader->readOuterXml()));

        self::$processData['numberOfCategories']++;

        // ignore invalid
        if (!isset($element->Ид)) {
            unset($element);

            return true;
        }

        // progress
        if (self::$processData['numberOfCategories'] < $_SESSION['IMPORT_1C']['numberOfCategories']) {
            unset($element);

            return true;
        }

        // already processed
        if (in_array((string) $element->Ид, $_SESSION['IMPORT_1C_PROCESS']['currentCategories1c'], true)) {
            unset($element);

            return true;
        }

        $category = Term::getTermIdByMeta((string) $element->Ид);

        /*
        <Группа>
            <Ид>dee6e199-55bc-11d9-848a-00112f43529a</Ид>
            <ПометкаУдаления>false</ПометкаУдаления>
            <Наименование>Телевизоры</Наименование>
        </Группа>
        */

        if (
            (string) $element->ПометкаУдаления &&
            (string) $element->ПометкаУдаления === 'true'
        ) {
            if ($category) {
                \wp_delete_term($category, 'product_cat');
            }

            unset($element);

            return true;
        }

        $_SESSION['IMPORT_1C']['categoryIdStack'] = self::$processData['categoryIdStack'];

        $categoryEntry = [
            'parent' => self::$processData['categoryIdStack'][0],
            'name' => trim(wp_strip_all_tags((string) $element->Наименование))
        ];

        if (!$category) {
            $category = apply_filters(
                'itglx_wc1c_find_product_cat_term_id',
                $category,
                $element,
                'product_cat',
                $categoryEntry['parent']
            );

            if ($category) {
                Logger::logChanges(
                    '(product_cat) Found through filter `itglx_wc1c_find_product_cat_term_id`, `term_id` - '
                        . $category,
                    [(string) $element->Ид]
                );
                Term::update1cId($category, (string) $element->Ид);
            }
        }

        if ($category) {
            $categoryEntry['term_id'] = $category;
        }

        if (isset($categoryEntry['term_id'])) {
            Term::updateProductCat($categoryEntry);
            Logger::logChanges(
                '(product_cat) Updated term `term_id` - ' . $categoryEntry['term_id'],
                [(string) $element->Ид]
            );
        } else {
            $categoryEntry['term_id'] = Term::insertProductCat($categoryEntry);
            Term::update1cId($categoryEntry['term_id'], (string) $element->Ид);
            Logger::logChanges(
                '(product_cat) Added term `term_id` - ' . $categoryEntry['term_id'],
                [(string) $element->Ид]
            );
        }

        self::$processData['currentCategoryId'] = $categoryEntry['term_id'];

        // save current change group list
        $_SESSION['IMPORT_1C_PROCESS']['currentCategories1c'][] = (string) $element->Ид;

        if (self::$saveGroupListToOption) {
            update_option('currentAll1cGroup', $_SESSION['IMPORT_1C_PROCESS']['currentCategories1c']);
        }

        $_SESSION['IMPORT_1C']['currentCategoryId'] = self::$processData['currentCategoryId'];
        $_SESSION['IMPORT_1C']['numberOfCategories'] = self::$processData['numberOfCategories'];

        unset($element, $categoryEntry);

        return true;
    }

    /**
     * Checking whether the processing of product categories is disabled in the settings.
     *
     * @return bool
     */
    public static function isDisabled()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['skip_categories'])) {
            return true;
        }

        return false;
    }

    /**
     * Allows you to check if groups have already been processed or not.
     *
     * @return bool
     */
    public static function isParsed()
    {
        if (isset($_SESSION['IMPORT_1C']['categoryIsParsed'])) {
            return true;
        }

        return false;
    }

    /**
     * Sets the flag that groups have been processed.
     *
     * @return void
     */
    public static function setParsed()
    {
        $_SESSION['IMPORT_1C']['categoryIsParsed'] = true;
    }

    /**
     * Preparing variables before processing groups.
     *
     * @return void
     */
    public static function prepare()
    {
        if (!isset($_SESSION['IMPORT_1C']['numberOfCategories'])) {
            $_SESSION['IMPORT_1C']['numberOfCategories'] = 0;
        }

        if (!isset($_SESSION['IMPORT_1C_PROCESS']['currentCategories1c'])) {
            $_SESSION['IMPORT_1C_PROCESS']['currentCategories1c'] = [];
        }

        self::$processData = [
            'numberOfCategories' => 0,
            'currentCategoryId' => isset($_SESSION['IMPORT_1C']['currentCategoryId'])
                ? $_SESSION['IMPORT_1C']['currentCategoryId']
                : 0,
            'categoryIdStack' => isset($_SESSION['IMPORT_1C']['categoryIdStack'])
                ? $_SESSION['IMPORT_1C']['categoryIdStack']
                : []
        ];
    }
}
