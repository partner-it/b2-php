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
	protected $B2Client;

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

}
