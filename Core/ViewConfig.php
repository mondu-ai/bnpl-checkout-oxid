<?php

namespace OxidEsales\MonduPayment\Core;

class ViewConfig extends ViewConfig_parent
{
	protected $config = null;

	public function getWidgetUrl()
	{
		$url = $this->getMonduConfig()->getWidgetUrl();
		return $url;
	}

	protected function getMonduConfig()
	{
		if (is_null($this->config)) {
			$this->config = oxNew(\OxidEsales\MonduPayment\Core\Config::class);
		}

		return $this->config;
	}
}
