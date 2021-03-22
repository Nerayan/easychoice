<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

/**
 * Using a filter to check whether an product has been deleted or not according to XML data.
 */
class ProductIsRemoved
{
    private static $instance = false;

    /**
     * Returns an instance of a class or creates a new instance if it doesn't exist.
     *
     * @return ProductIsRemoved
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create new instance.
     *
     * @link https://developer.wordpress.org/reference/functions/add_filter/
     *
     * @return void
     */
    private function __construct()
    {
        add_filter('itglx_wc1c_product_is_removed', [$this, 'process'], 10, 2);
    }

    /**
     * Filter callback. Checking the signs of product removal.
     *
     * Example xml structure (position Каталог -> Товары -> Товар)
     *
     * ```xml
     * <Товар Статус="Удален">
     *    ...
     * </Товар>
     * <Товар>
     *    <ПометкаУдаления>true</ПометкаУдаления>
     *    ...
     * </Товар>
     * <Товар>
     *    <ПометкаУдаления>Да</ПометкаУдаления>
     *    ...
     * </Товар>
     *
     * @param bool $isRemoved
     * @param \SimpleXMLElement $element Node object "Товар".
     *
     * @return bool
     */
    public function process($isRemoved, \SimpleXMLElement $element)
    {
        if ($isRemoved) {
            return $isRemoved;
        }

        if ((string) $element->ПометкаУдаления && in_array((string) $element->ПометкаУдаления, ['true', 'Да'], true)) {
            return true;
        }

        if (isset($element['Статус']) && (string) $element['Статус'] === 'Удален') {
            return true;
        }

        return $isRemoved;
    }
}
