<?php
namespace ILABAmazon\SageMaker;

use ILABAmazon\AwsClient;

/**
 * This client is used to interact with the **Amazon SageMaker Service** service.
 * @method \ILABAmazon\Result addTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \ILABAmazon\Result createAlgorithm(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createAlgorithmAsync(array $args = [])
 * @method \ILABAmazon\Result createCodeRepository(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createCodeRepositoryAsync(array $args = [])
 * @method \ILABAmazon\Result createCompilationJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createCompilationJobAsync(array $args = [])
 * @method \ILABAmazon\Result createEndpoint(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createEndpointAsync(array $args = [])
 * @method \ILABAmazon\Result createEndpointConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createEndpointConfigAsync(array $args = [])
 * @method \ILABAmazon\Result createHyperParameterTuningJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createHyperParameterTuningJobAsync(array $args = [])
 * @method \ILABAmazon\Result createLabelingJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createLabelingJobAsync(array $args = [])
 * @method \ILABAmazon\Result createModel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createModelAsync(array $args = [])
 * @method \ILABAmazon\Result createModelPackage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createModelPackageAsync(array $args = [])
 * @method \ILABAmazon\Result createNotebookInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createNotebookInstanceAsync(array $args = [])
 * @method \ILABAmazon\Result createNotebookInstanceLifecycleConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createNotebookInstanceLifecycleConfigAsync(array $args = [])
 * @method \ILABAmazon\Result createPresignedNotebookInstanceUrl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createPresignedNotebookInstanceUrlAsync(array $args = [])
 * @method \ILABAmazon\Result createTrainingJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTrainingJobAsync(array $args = [])
 * @method \ILABAmazon\Result createTransformJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTransformJobAsync(array $args = [])
 * @method \ILABAmazon\Result createWorkteam(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createWorkteamAsync(array $args = [])
 * @method \ILABAmazon\Result deleteAlgorithm(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteAlgorithmAsync(array $args = [])
 * @method \ILABAmazon\Result deleteCodeRepository(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteCodeRepositoryAsync(array $args = [])
 * @method \ILABAmazon\Result deleteEndpoint(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteEndpointAsync(array $args = [])
 * @method \ILABAmazon\Result deleteEndpointConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteEndpointConfigAsync(array $args = [])
 * @method \ILABAmazon\Result deleteModel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteModelAsync(array $args = [])
 * @method \ILABAmazon\Result deleteModelPackage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteModelPackageAsync(array $args = [])
 * @method \ILABAmazon\Result deleteNotebookInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteNotebookInstanceAsync(array $args = [])
 * @method \ILABAmazon\Result deleteNotebookInstanceLifecycleConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteNotebookInstanceLifecycleConfigAsync(array $args = [])
 * @method \ILABAmazon\Result deleteTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTagsAsync(array $args = [])
 * @method \ILABAmazon\Result deleteWorkteam(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteWorkteamAsync(array $args = [])
 * @method \ILABAmazon\Result describeAlgorithm(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeAlgorithmAsync(array $args = [])
 * @method \ILABAmazon\Result describeCodeRepository(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeCodeRepositoryAsync(array $args = [])
 * @method \ILABAmazon\Result describeCompilationJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeCompilationJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeEndpoint(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeEndpointAsync(array $args = [])
 * @method \ILABAmazon\Result describeEndpointConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeEndpointConfigAsync(array $args = [])
 * @method \ILABAmazon\Result describeHyperParameterTuningJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeHyperParameterTuningJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeLabelingJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeLabelingJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeModel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeModelAsync(array $args = [])
 * @method \ILABAmazon\Result describeModelPackage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeModelPackageAsync(array $args = [])
 * @method \ILABAmazon\Result describeNotebookInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeNotebookInstanceAsync(array $args = [])
 * @method \ILABAmazon\Result describeNotebookInstanceLifecycleConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeNotebookInstanceLifecycleConfigAsync(array $args = [])
 * @method \ILABAmazon\Result describeSubscribedWorkteam(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSubscribedWorkteamAsync(array $args = [])
 * @method \ILABAmazon\Result describeTrainingJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTrainingJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeTransformJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTransformJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeWorkteam(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeWorkteamAsync(array $args = [])
 * @method \ILABAmazon\Result getSearchSuggestions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSearchSuggestionsAsync(array $args = [])
 * @method \ILABAmazon\Result listAlgorithms(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listAlgorithmsAsync(array $args = [])
 * @method \ILABAmazon\Result listCodeRepositories(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listCodeRepositoriesAsync(array $args = [])
 * @method \ILABAmazon\Result listCompilationJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listCompilationJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listEndpointConfigs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEndpointConfigsAsync(array $args = [])
 * @method \ILABAmazon\Result listEndpoints(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEndpointsAsync(array $args = [])
 * @method \ILABAmazon\Result listHyperParameterTuningJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listHyperParameterTuningJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listLabelingJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listLabelingJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listLabelingJobsForWorkteam(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listLabelingJobsForWorkteamAsync(array $args = [])
 * @method \ILABAmazon\Result listModelPackages(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listModelPackagesAsync(array $args = [])
 * @method \ILABAmazon\Result listModels(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listModelsAsync(array $args = [])
 * @method \ILABAmazon\Result listNotebookInstanceLifecycleConfigs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listNotebookInstanceLifecycleConfigsAsync(array $args = [])
 * @method \ILABAmazon\Result listNotebookInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listNotebookInstancesAsync(array $args = [])
 * @method \ILABAmazon\Result listSubscribedWorkteams(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSubscribedWorkteamsAsync(array $args = [])
 * @method \ILABAmazon\Result listTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \ILABAmazon\Result listTrainingJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTrainingJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listTrainingJobsForHyperParameterTuningJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTrainingJobsForHyperParameterTuningJobAsync(array $args = [])
 * @method \ILABAmazon\Result listTransformJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTransformJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listWorkteams(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkteamsAsync(array $args = [])
 * @method \ILABAmazon\Result renderUiTemplate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise renderUiTemplateAsync(array $args = [])
 * @method \ILABAmazon\Result search(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchAsync(array $args = [])
 * @method \ILABAmazon\Result startNotebookInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startNotebookInstanceAsync(array $args = [])
 * @method \ILABAmazon\Result stopCompilationJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopCompilationJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopHyperParameterTuningJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopHyperParameterTuningJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopLabelingJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopLabelingJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopNotebookInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopNotebookInstanceAsync(array $args = [])
 * @method \ILABAmazon\Result stopTrainingJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopTrainingJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopTransformJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopTransformJobAsync(array $args = [])
 * @method \ILABAmazon\Result updateCodeRepository(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateCodeRepositoryAsync(array $args = [])
 * @method \ILABAmazon\Result updateEndpoint(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateEndpointAsync(array $args = [])
 * @method \ILABAmazon\Result updateEndpointWeightsAndCapacities(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateEndpointWeightsAndCapacitiesAsync(array $args = [])
 * @method \ILABAmazon\Result updateNotebookInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateNotebookInstanceAsync(array $args = [])
 * @method \ILABAmazon\Result updateNotebookInstanceLifecycleConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateNotebookInstanceLifecycleConfigAsync(array $args = [])
 * @method \ILABAmazon\Result updateWorkteam(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateWorkteamAsync(array $args = [])
 */
class SageMakerClient extends AwsClient {}
