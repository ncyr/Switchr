<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Library for S3 operations.
 * Usage:
 * $data['key'] = 'AKIAIY766J6N7WOLUGFA';
 * $data['secret'] = '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';
 * $data['region'] = 'us-east-1';
 * $s3 = new $this->s3($data);
 * $s3->bucketExists('testy-testy-test');
 * @param  array  $data  ['key', 'secret', 'region']
 */
class S3
{
    private $key;
    private $secret;
    private $credentials;
    private $client;

    public function __construct($data)
    {
        // Require the AWS SDK.
        require_once 'aws.phar';

        $this->key = $data['key'];
        $this->secret = $data['secret'];
        $this->region = $data['region'];

        # Other methods of providing the credentials did not work, so stick to this.
        $credentials = new Aws\Credentials\Credentials($this->key, $this->secret);

        # Instantiate the S3 client with AWS credentials
        $this->client = new Aws\S3\S3Client([
            'version'     => '2006-03-01',
            'region'      => "{$this->region}",  // It seems that the AWS library requires quotes here.
            'credentials' => $credentials
        ]);
    }

    /**
     * Check to see if credentials are correct.
     * The only way to tell is to make a request to AWS.
     * If this function returns false then credentials are incorrect.
     * @return  string  False if credentials are incorrect.
     */
    public function credsCorrect()
    {
        return $this->client->listBuckets([]);
    }

    /**
     * Check to see if bucket exists.
     * @param   string  $bucket_name  Name of bucket to check.
     * @return  bool                  True if bucket exists, false if bucket does not exist.
     */
    public function bucketExists($bucket_name)
    {
        if ($this->client->doesBucketExist($bucket_name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create a bucket with versioning.
     * @param   string  $bucket_name  Name of bucket to create.
     * @return  bool  True if bucket is created and versioning enabled, otherwise false.
     */
    public function createBucket($bucket_name)
    {
        if ($this->bucketExists($bucket_name)) {
            return false;
        }
        // Create bucket.
        try {
            $result = $this->client->createBucket([
                'Bucket' => $bucket_name,
                'CreateBucketConfiguration' => [
                    'LocationConstraint' => $this->region,
                ],
            ]);
        } catch (Aws\Exception\AwsException $e) {
            // If creation fails return false,
            // but we're mainly doing this to check for unused bucket name.
            return false;
        }

        $this->versionBucket($bucket_name);
        return true;
    }

    /**
     * Enables versioning for bucket.
     * @param   string  $bucket_name  Name of bucket to create.
     */
    public function versionBucket($bucket_name)
    {
        $this->client->putBucketVersioning([
            'Bucket' => $bucket_name,
            'VersioningConfiguration' => [
                'MFADelete' => 'Disabled',
                'Status' => 'Enabled',
            ],
        ]);
    }

    /**
     * Deletes the bucket.
     * All objects (including all object versions and Delete Markers) in the bucket
     * must be deleted before the bucket itself can be deleted.
     * @param   string  $bucket_name  Name of bucket to delete.
     */
    public function deleteBucket($bucket_name)
    {
        // Get all objects and versions.
        $all_objects = $this->getAllObjectVersions($bucket_name);
        // Split the array into 1,000 objects each, which is the limit for AWS requests.
        $split_array = array_chunk($all_objects, 1000, true);

        // For each 1,000 objects.
        foreach ($split_array as $split => $elements) {
            // Empty the array on each iteration.
            $delete_array = array();
            // Convert the 1,000 objects into a valid AWS Delete array.
            foreach ($elements as $version_id => $key_id) {
                $delete_array[] = array('Key' => $key_id, 'VersionId' => $version_id);
            }
            // Delete 1,000 objects.
            $this->client->deleteObjects([
                'Bucket' => $bucket_name,
                'Delete' => [
                    'Objects' => $delete_array,
                    'Quiet' => false,
                ],
            ]);
        }
        // Delete the bucket.
        $this->client->deleteBucket(['Bucket' => $bucket_name]);
    }

    /**
     * Recursive function to get around the 1,000 object limit in AWS GET request.
     * @param   string  $bucket_name  Name of bucket.
     * @param   string  $key_id       NextKeyMarker.
     * @param   string  $version_id   NextVersionIdMarker.
     * @param   array   $all          The previously-built array that gets passed through again.
     * @return  array                 All versioned objects: $array['VersionId']['Key']
     */
    private function getAllObjectVersions($bucket_name, $key_id = null, $version_id = null, $all = null)
    {
        $objects = $this->client->listObjectVersions(array('Bucket' => $bucket_name, 'KeyMarker' => $key_id, 'VersionIdMarker' => $version_id));
        foreach ($objects['Versions'] as $e) {
            $all[$e['VersionId']] = $e['Key'];
        }
        if ($objects['IsTruncated']) {
            $all = $this->getAllObjectVersions($bucket_name, $objects['NextKeyMarker'], $objects['NextVersionIdMarker'], $all);
        }
        return $all;
    }
}
