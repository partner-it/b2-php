<?php
namespace B2\Files;

use B2\B2Client;

/**
 * Class Files
 * @package Mlh\B2\Files
 */
class Files
{

    /**
     * @var B2Client
     */
    public $B2Client;

    /**
     * Files constructor.
     * @param B2Client $B2Client
     */
    public function __construct(B2Client $B2Client)
    {
        $this->B2Client = $B2Client;
    }

    /**
     * @param $bucketId
     * @param $startFileName
     * @return mixed
     * @throws \Exception
     */
    public function listFileNames($bucketId, $startFileName = null, $maxFileCount = 100)
    {
        return $this->B2Client->call('b2_list_file_names', 'POST', [
            'startFileName' => $startFileName,
            'bucketId'      => $bucketId,
            'maxFileCount'  => $maxFileCount
        ]);
    }

    /**
     * Lists all files in a bucket and starting filename. For very large buckets this may fail, use listFileNames() directly
     *
     * @param $bucketId
     * @param $startFileName
     * @return array
     */
    public function listAllFileNames($bucketId, $startFileName)
    {

        $allresults = [];
        $result     = $this->listFileNames($bucketId, $startFileName);
        $allresults = array_merge($allresults, $result['responseBody']['files']);

        if ($result['responseBody']['nextFileName'] !== null) {
            $allresults = array_merge($allresults,
                $this->listAllFileNames($bucketId, $result['responseBody']['nextFileName']));
        }

        return $allresults;
    }

    /**
     * @param $fileID
     * @return mixed
     * @throws \Exception
     */
    public function getFileInfo($fileId)
    {
        return $this->B2Client->call('b2_get_file_info', 'POST', ['fileId' => $fileId]);
    }

    /**
     * @param $bucketId
     * @return mixed
     * @throws \Exception
     */
    public function getUploadUrl($bucketId)
    {
        return $this->B2Client->call('b2_get_upload_url', 'POST', ['bucketId' => $bucketId]);
    }

    /**
     * @param $bucketId
     * @param string $filePath The path to the local file
     * @param string $fileName The name/path on B2
     * @param string $contentType
     * @param array $uploadUrlResponse
     * @return array
     */
    public function uploadFile($bucketId, $filePath, $fileName, $contentType, $uploadUrlResponse = [])
    {

        if (empty($uploadUrlResponse)) {
            $uploadUrlResponse = $this->getUploadUrl($bucketId);
        }

        $handle       = fopen($filePath, 'r');
        $fileData     = fread($handle, filesize($filePath));
        $fileDataSha1 = sha1_file($filePath);

        return $this->B2Client->uploadData($fileData, $fileDataSha1, $fileName, $contentType,
            $uploadUrlResponse['uploadUrl'],
            $uploadUrlResponse['authorizationToken']);

    }

    /**
     * @param $bucketId
     * @param $fileName
     * @return mixed
     * @throws \Exception
     */
    public function downloadFileByName($bucketName, $fileName)
    {
        return $this->B2Client->downloadFileByName($bucketName . '/' . $fileName);
    }

}
