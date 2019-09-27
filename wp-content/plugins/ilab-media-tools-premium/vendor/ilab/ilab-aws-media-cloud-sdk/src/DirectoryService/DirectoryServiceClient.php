<?php
namespace ILABAmazon\DirectoryService;

use ILABAmazon\AwsClient;

/**
 * AWS Directory Service client
 *
 * @method \ILABAmazon\Result acceptSharedDirectory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise acceptSharedDirectoryAsync(array $args = [])
 * @method \ILABAmazon\Result addIpRoutes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addIpRoutesAsync(array $args = [])
 * @method \ILABAmazon\Result addTagsToResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \ILABAmazon\Result cancelSchemaExtension(array $args = [])
 * @method \GuzzleHttp\Promise\Promise cancelSchemaExtensionAsync(array $args = [])
 * @method \ILABAmazon\Result connectDirectory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise connectDirectoryAsync(array $args = [])
 * @method \ILABAmazon\Result createAlias(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createAliasAsync(array $args = [])
 * @method \ILABAmazon\Result createComputer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createComputerAsync(array $args = [])
 * @method \ILABAmazon\Result createConditionalForwarder(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createConditionalForwarderAsync(array $args = [])
 * @method \ILABAmazon\Result createDirectory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDirectoryAsync(array $args = [])
 * @method \ILABAmazon\Result createLogSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createLogSubscriptionAsync(array $args = [])
 * @method \ILABAmazon\Result createMicrosoftAD(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createMicrosoftADAsync(array $args = [])
 * @method \ILABAmazon\Result createSnapshot(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSnapshotAsync(array $args = [])
 * @method \ILABAmazon\Result createTrust(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTrustAsync(array $args = [])
 * @method \ILABAmazon\Result deleteConditionalForwarder(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteConditionalForwarderAsync(array $args = [])
 * @method \ILABAmazon\Result deleteDirectory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDirectoryAsync(array $args = [])
 * @method \ILABAmazon\Result deleteLogSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteLogSubscriptionAsync(array $args = [])
 * @method \ILABAmazon\Result deleteSnapshot(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteSnapshotAsync(array $args = [])
 * @method \ILABAmazon\Result deleteTrust(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTrustAsync(array $args = [])
 * @method \ILABAmazon\Result deregisterEventTopic(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deregisterEventTopicAsync(array $args = [])
 * @method \ILABAmazon\Result describeConditionalForwarders(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeConditionalForwardersAsync(array $args = [])
 * @method \ILABAmazon\Result describeDirectories(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDirectoriesAsync(array $args = [])
 * @method \ILABAmazon\Result describeDomainControllers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDomainControllersAsync(array $args = [])
 * @method \ILABAmazon\Result describeEventTopics(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeEventTopicsAsync(array $args = [])
 * @method \ILABAmazon\Result describeSharedDirectories(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSharedDirectoriesAsync(array $args = [])
 * @method \ILABAmazon\Result describeSnapshots(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSnapshotsAsync(array $args = [])
 * @method \ILABAmazon\Result describeTrusts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTrustsAsync(array $args = [])
 * @method \ILABAmazon\Result disableRadius(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disableRadiusAsync(array $args = [])
 * @method \ILABAmazon\Result disableSso(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disableSsoAsync(array $args = [])
 * @method \ILABAmazon\Result enableRadius(array $args = [])
 * @method \GuzzleHttp\Promise\Promise enableRadiusAsync(array $args = [])
 * @method \ILABAmazon\Result enableSso(array $args = [])
 * @method \GuzzleHttp\Promise\Promise enableSsoAsync(array $args = [])
 * @method \ILABAmazon\Result getDirectoryLimits(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDirectoryLimitsAsync(array $args = [])
 * @method \ILABAmazon\Result getSnapshotLimits(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSnapshotLimitsAsync(array $args = [])
 * @method \ILABAmazon\Result listIpRoutes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listIpRoutesAsync(array $args = [])
 * @method \ILABAmazon\Result listLogSubscriptions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listLogSubscriptionsAsync(array $args = [])
 * @method \ILABAmazon\Result listSchemaExtensions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSchemaExtensionsAsync(array $args = [])
 * @method \ILABAmazon\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ILABAmazon\Result registerEventTopic(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerEventTopicAsync(array $args = [])
 * @method \ILABAmazon\Result rejectSharedDirectory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise rejectSharedDirectoryAsync(array $args = [])
 * @method \ILABAmazon\Result removeIpRoutes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeIpRoutesAsync(array $args = [])
 * @method \ILABAmazon\Result removeTagsFromResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 * @method \ILABAmazon\Result resetUserPassword(array $args = [])
 * @method \GuzzleHttp\Promise\Promise resetUserPasswordAsync(array $args = [])
 * @method \ILABAmazon\Result restoreFromSnapshot(array $args = [])
 * @method \GuzzleHttp\Promise\Promise restoreFromSnapshotAsync(array $args = [])
 * @method \ILABAmazon\Result shareDirectory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise shareDirectoryAsync(array $args = [])
 * @method \ILABAmazon\Result startSchemaExtension(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startSchemaExtensionAsync(array $args = [])
 * @method \ILABAmazon\Result unshareDirectory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise unshareDirectoryAsync(array $args = [])
 * @method \ILABAmazon\Result updateConditionalForwarder(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateConditionalForwarderAsync(array $args = [])
 * @method \ILABAmazon\Result updateNumberOfDomainControllers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateNumberOfDomainControllersAsync(array $args = [])
 * @method \ILABAmazon\Result updateRadius(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRadiusAsync(array $args = [])
 * @method \ILABAmazon\Result updateTrust(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateTrustAsync(array $args = [])
 * @method \ILABAmazon\Result verifyTrust(array $args = [])
 * @method \GuzzleHttp\Promise\Promise verifyTrustAsync(array $args = [])
 */
class DirectoryServiceClient extends AwsClient {}
