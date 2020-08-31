<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/api/servicemanagement/v1/resources.proto

namespace MediaCloud\Vendor\Google\Cloud\ServiceManagement\V1\ConfigFile;

use UnexpectedValueException;

/**
 * Protobuf type <code>google.api.servicemanagement.v1.ConfigFile.FileType</code>
 */
class FileType
{
    /**
     * Unknown file type.
     *
     * Generated from protobuf enum <code>FILE_TYPE_UNSPECIFIED = 0;</code>
     */
    const FILE_TYPE_UNSPECIFIED = 0;
    /**
     * YAML-specification of service.
     *
     * Generated from protobuf enum <code>SERVICE_CONFIG_YAML = 1;</code>
     */
    const SERVICE_CONFIG_YAML = 1;
    /**
     * OpenAPI specification, serialized in JSON.
     *
     * Generated from protobuf enum <code>OPEN_API_JSON = 2;</code>
     */
    const OPEN_API_JSON = 2;
    /**
     * OpenAPI specification, serialized in YAML.
     *
     * Generated from protobuf enum <code>OPEN_API_YAML = 3;</code>
     */
    const OPEN_API_YAML = 3;
    /**
     * FileDescriptorSet, generated by protoc.
     * To generate, use protoc with imports and source info included.
     * For an example test.proto file, the following command would put the value
     * in a new file named out.pb.
     * $protoc --include_imports --include_source_info test.proto -o out.pb
     *
     * Generated from protobuf enum <code>FILE_DESCRIPTOR_SET_PROTO = 4;</code>
     */
    const FILE_DESCRIPTOR_SET_PROTO = 4;
    /**
     * Uncompiled Proto file. Used for storage and display purposes only,
     * currently server-side compilation is not supported. Should match the
     * inputs to 'protoc' command used to generated FILE_DESCRIPTOR_SET_PROTO. A
     * file of this type can only be included if at least one file of type
     * FILE_DESCRIPTOR_SET_PROTO is included.
     *
     * Generated from protobuf enum <code>PROTO_FILE = 6;</code>
     */
    const PROTO_FILE = 6;

    private static $valueToName = [
        self::FILE_TYPE_UNSPECIFIED => 'FILE_TYPE_UNSPECIFIED',
        self::SERVICE_CONFIG_YAML => 'SERVICE_CONFIG_YAML',
        self::OPEN_API_JSON => 'OPEN_API_JSON',
        self::OPEN_API_YAML => 'OPEN_API_YAML',
        self::FILE_DESCRIPTOR_SET_PROTO => 'FILE_DESCRIPTOR_SET_PROTO',
        self::PROTO_FILE => 'PROTO_FILE',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(FileType::class, \MediaCloud\Vendor\Google\Cloud\ServiceManagement\V1\ConfigFile_FileType::class);

