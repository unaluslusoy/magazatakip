<?php

namespace app\Services\Tamsoft;

use app\Models\TamsoftStockRepo;
use app\Models\TamsoftStockConfig;
use app\Http\TamsoftHttpClient;
use app\Utils\RateLimiter;
use app\Services\TamsoftStockService;

/**
 * Depo bazlı stok senkron servisi (iskele).
 */
class DepotSyncService
{
	private TamsoftStockRepo $repo;
	private TamsoftStockConfig $cfg;
	private TamsoftHttpClient $http;
	private RateLimiter $limiter;
	private TamsoftStockService $legacy;

	public function __construct()
	{
		$this->repo = new TamsoftStockRepo();
		$this->cfg = new TamsoftStockConfig();
		$this->http = new TamsoftHttpClient();
		$intervalMs = (int)($this->cfg->getConfig()['throttle_ms'] ?? 75);
		$this->limiter = new RateLimiter(max(0, $intervalMs));
		$this->legacy = new TamsoftStockService();
	}

	public function ping(): array
	{
		return ['success'=>true, 'service'=>'DepotSyncService'];
	}

	public function refreshStocks(?string $date = null, ?int $depoId = null, bool $onlyPositive = true, ?bool $lastBarcodeOnly = null, ?bool $onlyEcommerce = null): array
	{
		return $this->legacy->refreshStocks($date, $depoId, $onlyPositive, $lastBarcodeOnly, $onlyEcommerce);
	}

	public function refreshDepotQtyFromEcommerce(?string $date = null, ?int $depoId = null, int $maxPages = 200, int $batch = 100): array
	{
		return $this->legacy->refreshDepotQtyFromEcommerce($date, $depoId, $maxPages, $batch);
	}
}


