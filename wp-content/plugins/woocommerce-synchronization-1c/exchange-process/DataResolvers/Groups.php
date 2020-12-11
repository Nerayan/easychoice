<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class Groups
{
    public static function process($reader, $processData)
    {
        if (!isset($_SESSION['IMPORT_1C']['numberOfCategories'])) {
            $_SESSION['IMPORT_1C']['numberOfCategories'] = 0;
        }

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
            return $processData;
        }

        // if start new block, add the current category as the last parent on the stack
        if ($reader->name === 'Группы' && $reader->nodeType === \XMLReader::ELEMENT) {
            if ($processData['numberOfCategories'] >= $_SESSION['IMPORT_1C']['numberOfCategories']) {
                array_unshift($processData['categoryIdStack'], $processData['currentCategoryId']);
            }
        }

        // if end block, remove the last parent from the stack, as the level of nesting has changed
        // and you need to bind from the previous level
        if ($reader->name === 'Группы' && $reader->nodeType === \XMLReader::END_ELEMENT) {
            if ($processData['numberOfCategories'] >= $_SESSION['IMPORT_1C']['numberOfCategories']) {
                array_shift($processData['categoryIdStack']);
            }
        }

        if ($reader->name !== 'Группа' || $reader->nodeType !== \XMLReader::ELEMENT) {
            return $processData;
        }

        // check time execution limit
        if (!HeartBeat::nextTerm()) {
            return false;
        }

        $element = simplexml_load_string(trim($reader->readOuterXml()));

        $processData['numberOfCategories']++;

        // ignore invalid
        if (!isset($element->Ид)) {
            unset($element);

            return $processData;
        }

        // progress
        if ($processData['numberOfCategories'] < $_SESSION['IMPORT_1C']['numberOfCategories']) {
            unset($element);

            return $processData;
        }

        // already processed
        if (in_array((string) $element->Ид, $_SESSION['IMPORT_1C_PROCESS']['currentCategorys1c'], true)) {
            unset($element);

            return $processData;
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

            return $processData;
        }

        $_SESSION['IMPORT_1C']['categoryIdStack'] = $processData['categoryIdStack'];

        $categoryEntry = [
            'parent' => $processData['categoryIdStack'][0],
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

        $processData['currentCategoryId'] = $categoryEntry['term_id'];
        $_SESSION['IMPORT_1C_PROCESS']['currentCategorys1c'][] = (string) $element->Ид;
        $_SESSION['IMPORT_1C']['currentCategoryId'] = $processData['currentCategoryId'];
        $_SESSION['IMPORT_1C']['numberOfCategories'] = $processData['numberOfCategories'];

        unset($element, $categoryEntry);

        return $processData;
    }
}
