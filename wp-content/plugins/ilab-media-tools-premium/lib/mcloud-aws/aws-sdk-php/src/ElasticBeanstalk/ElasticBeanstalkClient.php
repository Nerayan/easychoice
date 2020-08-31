<?php

namespace MediaCloud\Vendor\Aws\ElasticBeanstalk;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Elastic Beanstalk** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result abortEnvironmentUpdate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise abortEnvironmentUpdateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result applyEnvironmentManagedAction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise applyEnvironmentManagedActionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result associateEnvironmentOperationsRole(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateEnvironmentOperationsRoleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result checkDNSAvailability(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise checkDNSAvailabilityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result composeEnvironments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise composeEnvironmentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createApplicationVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createApplicationVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createConfigurationTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createConfigurationTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPlatformVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPlatformVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createStorageLocation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createStorageLocationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteApplicationVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteApplicationVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteConfigurationTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteConfigurationTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEnvironmentConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEnvironmentConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePlatformVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePlatformVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAccountAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAccountAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeApplicationVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeApplicationVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeApplications(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeApplicationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeConfigurationOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeConfigurationOptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeConfigurationSettings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeConfigurationSettingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEnvironmentHealth(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEnvironmentHealthAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEnvironmentManagedActionHistory(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEnvironmentManagedActionHistoryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEnvironmentManagedActions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEnvironmentManagedActionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEnvironmentResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEnvironmentResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEnvironments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEnvironmentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeInstancesHealth(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeInstancesHealthAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describePlatformVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describePlatformVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateEnvironmentOperationsRole(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateEnvironmentOperationsRoleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAvailableSolutionStacks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAvailableSolutionStacksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPlatformBranches(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPlatformBranchesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPlatformVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPlatformVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rebuildEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rebuildEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result requestEnvironmentInfo(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise requestEnvironmentInfoAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result restartAppServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restartAppServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result retrieveEnvironmentInfo(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise retrieveEnvironmentInfoAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result swapEnvironmentCNAMEs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise swapEnvironmentCNAMEsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result terminateEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise terminateEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateApplicationResourceLifecycle(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateApplicationResourceLifecycleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateApplicationVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateApplicationVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateConfigurationTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateConfigurationTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result validateConfigurationSettings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise validateConfigurationSettingsAsync(array $args = [])
 */
class ElasticBeanstalkClient extends AwsClient {}