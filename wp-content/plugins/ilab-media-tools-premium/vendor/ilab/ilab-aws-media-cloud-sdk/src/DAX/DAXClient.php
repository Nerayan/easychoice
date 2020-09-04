<?php
namespace ILABAmazon\DAX;

use ILABAmazon\AwsClient;

/**
 * This client is used to interact with the **Amazon DynamoDB Accelerator (DAX)** service.
 * @method \ILABAmazon\Result createCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \ILABAmazon\Result createParameterGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createParameterGroupAsync(array $args = [])
 * @method \ILABAmazon\Result createSubnetGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSubnetGroupAsync(array $args = [])
 * @method \ILABAmazon\Result decreaseReplicationFactor(array $args = [])
 * @method \GuzzleHttp\Promise\Promise decreaseReplicationFactorAsync(array $args = [])
 * @method \ILABAmazon\Result deleteCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \ILABAmazon\Result deleteParameterGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteParameterGroupAsync(array $args = [])
 * @method \ILABAmazon\Result deleteSubnetGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteSubnetGroupAsync(array $args = [])
 * @method \ILABAmazon\Result describeClusters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeClustersAsync(array $args = [])
 * @method \ILABAmazon\Result describeDefaultParameters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDefaultParametersAsync(array $args = [])
 * @method \ILABAmazon\Result describeEvents(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \ILABAmazon\Result describeParameterGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeParameterGroupsAsync(array $args = [])
 * @method \ILABAmazon\Result describeParameters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeParametersAsync(array $args = [])
 * @method \ILABAmazon\Result describeSubnetGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSubnetGroupsAsync(array $args = [])
 * @method \ILABAmazon\Result increaseReplicationFactor(array $args = [])
 * @method \GuzzleHttp\Promise\Promise increaseReplicationFactorAsync(array $args = [])
 * @method \ILABAmazon\Result listTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \ILABAmazon\Result rebootNode(array $args = [])
 * @method \GuzzleHttp\Promise\Promise rebootNodeAsync(array $args = [])
 * @method \ILABAmazon\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ILABAmazon\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ILABAmazon\Result updateCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateClusterAsync(array $args = [])
 * @method \ILABAmazon\Result updateParameterGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateParameterGroupAsync(array $args = [])
 * @method \ILABAmazon\Result updateSubnetGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateSubnetGroupAsync(array $args = [])
 */
class DAXClient extends AwsClient {}