<?php

namespace app\Services\Tamsoft;

use app\Models\TamsoftStockRepo;
use app\Models\TamsoftStockConfig;
use app\Http\TamsoftHttpClient;
use app\Services\TamsoftStockService;

/**
 * Ürün master senkron servisi (iskele). Mevcut TamsoftStockService ile çakışmayacak şekilde kurgulandı.
 * İlk aşamada yalnızca bağımsız çalışabilir yardımcı metotlar içerir.
 */
class MasterSyncService
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
		return ['success'=>true, 'service'=>'MasterSyncService'];
	}

	public function monthlyProductMasterSync(?int $preferredDepotId = null): array
	{
		return $this->legacy->monthlyProductMasterSync($preferredDepotId);
	}
}


