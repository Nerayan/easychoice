<?php

namespace MediaCloud\Vendor\Aws\Kms;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Key Management Service**.
 *
 * @method \MediaCloud\Vendor\Aws\Result cancelKeyDeletion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelKeyDeletionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result connectCustomKeyStore(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise connectCustomKeyStoreAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createAlias(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAliasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createCustomKeyStore(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCustomKeyStoreAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createGrant(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createGrantAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result decrypt(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise decryptAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAlias(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAliasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteCustomKeyStore(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCustomKeyStoreAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteImportedKeyMaterial(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteImportedKeyMaterialAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeCustomKeyStores(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCustomKeyStoresAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disableKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disableKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disableKeyRotation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disableKeyRotationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disconnectCustomKeyStore(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disconnectCustomKeyStoreAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result enableKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise enableKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result enableKeyRotation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise enableKeyRotationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result encrypt(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise encryptAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result generateDataKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise generateDataKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result generateDataKeyPair(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise generateDataKeyPairAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result generateDataKeyPairWithoutPlaintext(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise generateDataKeyPairWithoutPlaintextAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result generateDataKeyWithoutPlaintext(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise generateDataKeyWithoutPlaintextAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result generateRandom(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise generateRandomAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getKeyPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getKeyPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getKeyRotationStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getKeyRotationStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getParametersForImport(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getParametersForImportAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPublicKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPublicKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result importKeyMaterial(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise importKeyMaterialAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAliases(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAliasesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listGrants(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listGrantsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listKeyPolicies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listKeyPoliciesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listKeys(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listKeysAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listResourceTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listResourceTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRetirableGrants(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRetirableGrantsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putKeyPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putKeyPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result reEncrypt(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise reEncryptAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result retireGrant(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise retireGrantAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result revokeGrant(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise revokeGrantAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result scheduleKeyDeletion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise scheduleKeyDeletionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sign(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise signAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateAlias(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateAliasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateCustomKeyStore(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateCustomKeyStoreAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateKeyDescription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateKeyDescriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result verify(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise verifyAsync(array $args = [])
 */
class KmsClient extends AwsClient {}