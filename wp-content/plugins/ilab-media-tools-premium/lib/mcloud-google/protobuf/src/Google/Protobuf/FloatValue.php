<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/protobuf/wrappers.proto

namespace MediaCloud\Vendor\Google\Protobuf;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBType;
use MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBUtil;

/**
 * Wrapper message for `float`.
 * The JSON representation for `FloatValue` is JSON number.
 *
 * Generated from protobuf message <code>google.protobuf.FloatValue</code>
 */
class FloatValue extends \MediaCloud\Vendor\Google\Protobuf\Internal\Message
{
    /**
     * The float value.
     *
     * Generated from protobuf field <code>float value = 1;</code>
     */
    private $value = 0.0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type float $value
     *           The float value.
     * }
     */
    public function __construct($data = NULL) { \MediaCloud\Vendor\GPBMetadata\Google\Protobuf\Wrappers::initOnce();
        parent::__construct($data);
    }

    /**
     * The float value.
     *
     * Generated from protobuf field <code>float value = 1;</code>
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The float value.
     *
     * Generated from protobuf field <code>float value = 1;</code>
     * @param float $var
     * @return $this
     */
    public function setValue($var)
    {
        GPBUtil::checkFloat($var);
        $this->value = $var;

        return $this;
    }

}

