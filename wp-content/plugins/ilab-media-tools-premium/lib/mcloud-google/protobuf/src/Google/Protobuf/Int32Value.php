<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/protobuf/wrappers.proto

namespace MediaCloud\Vendor\Google\Protobuf;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBType;
use MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBUtil;

/**
 * Wrapper message for `int32`.
 * The JSON representation for `Int32Value` is JSON number.
 *
 * Generated from protobuf message <code>google.protobuf.Int32Value</code>
 */
class Int32Value extends \MediaCloud\Vendor\Google\Protobuf\Internal\Message
{
    /**
     * The int32 value.
     *
     * Generated from protobuf field <code>int32 value = 1;</code>
     */
    private $value = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $value
     *           The int32 value.
     * }
     */
    public function __construct($data = NULL) { \MediaCloud\Vendor\GPBMetadata\Google\Protobuf\Wrappers::initOnce();
        parent::__construct($data);
    }

    /**
     * The int32 value.
     *
     * Generated from protobuf field <code>int32 value = 1;</code>
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The int32 value.
     *
     * Generated from protobuf field <code>int32 value = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setValue($var)
    {
        GPBUtil::checkInt32($var);
        $this->value = $var;

        return $this;
    }

}

