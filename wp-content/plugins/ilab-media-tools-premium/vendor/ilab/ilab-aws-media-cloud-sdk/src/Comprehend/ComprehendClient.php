<?php
namespace ILABAmazon\Comprehend;

use ILABAmazon\AwsClient;

/**
 * This client is used to interact with the **Amazon Comprehend** service.
 * @method \ILABAmazon\Result batchDetectDominantLanguage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchDetectDominantLanguageAsync(array $args = [])
 * @method \ILABAmazon\Result batchDetectEntities(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchDetectEntitiesAsync(array $args = [])
 * @method \ILABAmazon\Result batchDetectKeyPhrases(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchDetectKeyPhrasesAsync(array $args = [])
 * @method \ILABAmazon\Result batchDetectSentiment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchDetectSentimentAsync(array $args = [])
 * @method \ILABAmazon\Result batchDetectSyntax(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchDetectSyntaxAsync(array $args = [])
 * @method \ILABAmazon\Result createDocumentClassifier(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDocumentClassifierAsync(array $args = [])
 * @method \ILABAmazon\Result createEntityRecognizer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createEntityRecognizerAsync(array $args = [])
 * @method \ILABAmazon\Result deleteDocumentClassifier(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDocumentClassifierAsync(array $args = [])
 * @method \ILABAmazon\Result deleteEntityRecognizer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteEntityRecognizerAsync(array $args = [])
 * @method \ILABAmazon\Result describeDocumentClassificationJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDocumentClassificationJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeDocumentClassifier(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDocumentClassifierAsync(array $args = [])
 * @method \ILABAmazon\Result describeDominantLanguageDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDominantLanguageDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeEntitiesDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeEntitiesDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeEntityRecognizer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeEntityRecognizerAsync(array $args = [])
 * @method \ILABAmazon\Result describeKeyPhrasesDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeKeyPhrasesDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeSentimentDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSentimentDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result describeTopicsDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTopicsDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result detectDominantLanguage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise detectDominantLanguageAsync(array $args = [])
 * @method \ILABAmazon\Result detectEntities(array $args = [])
 * @method \GuzzleHttp\Promise\Promise detectEntitiesAsync(array $args = [])
 * @method \ILABAmazon\Result detectKeyPhrases(array $args = [])
 * @method \GuzzleHttp\Promise\Promise detectKeyPhrasesAsync(array $args = [])
 * @method \ILABAmazon\Result detectSentiment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise detectSentimentAsync(array $args = [])
 * @method \ILABAmazon\Result detectSyntax(array $args = [])
 * @method \GuzzleHttp\Promise\Promise detectSyntaxAsync(array $args = [])
 * @method \ILABAmazon\Result listDocumentClassificationJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDocumentClassificationJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listDocumentClassifiers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDocumentClassifiersAsync(array $args = [])
 * @method \ILABAmazon\Result listDominantLanguageDetectionJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDominantLanguageDetectionJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listEntitiesDetectionJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEntitiesDetectionJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listEntityRecognizers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEntityRecognizersAsync(array $args = [])
 * @method \ILABAmazon\Result listKeyPhrasesDetectionJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listKeyPhrasesDetectionJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listSentimentDetectionJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSentimentDetectionJobsAsync(array $args = [])
 * @method \ILABAmazon\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ILABAmazon\Result listTopicsDetectionJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTopicsDetectionJobsAsync(array $args = [])
 * @method \ILABAmazon\Result startDocumentClassificationJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startDocumentClassificationJobAsync(array $args = [])
 * @method \ILABAmazon\Result startDominantLanguageDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startDominantLanguageDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result startEntitiesDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startEntitiesDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result startKeyPhrasesDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startKeyPhrasesDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result startSentimentDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startSentimentDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result startTopicsDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startTopicsDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopDominantLanguageDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopDominantLanguageDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopEntitiesDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopEntitiesDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopKeyPhrasesDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopKeyPhrasesDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopSentimentDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopSentimentDetectionJobAsync(array $args = [])
 * @method \ILABAmazon\Result stopTrainingDocumentClassifier(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopTrainingDocumentClassifierAsync(array $args = [])
 * @method \ILABAmazon\Result stopTrainingEntityRecognizer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopTrainingEntityRecognizerAsync(array $args = [])
 * @method \ILABAmazon\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ILABAmazon\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class ComprehendClient extends AwsClient {}
