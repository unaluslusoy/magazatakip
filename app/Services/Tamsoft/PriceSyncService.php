<?php

namespace app\Services\Tamsoft;

use app\Models\TamsoftStockRepo;
use app\Models\TamsoftStockConfig;
use app\Http\TamsoftHttpClient;
use app\Services\TamsoftStockService;

class PriceSyncService
{
	private TamsoftStockRepo $repo;
	private TamsoftStockConfig $cfg;
	private TamsoftHttpClient $http;
	private TamsoftStockService $legacy;

	public function __construct()
	{
		$this->repo = new TamsoftStockRepo();
		$this->cfg = new TamsoftStockConfig();
		$this->http = new TamsoftHttpClient();
		$this->legacy = new TamsoftStockService();
	}

	public function ping(): array
	{
		return ['success'=>true, 'service'=>'PriceSyncService'];
	}

	public function refreshPricesOnly(?string $date = null, ?int $depoId = null, int $maxPages = 200, int $batch = 100): array
	{
		return $this->legacy->refreshPricesOnly($date, $depoId, $maxPages, $batch);
	}
}


