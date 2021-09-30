<?php

namespace App\Packages\Integrations\DirectAdminCommand;

trait PackageCommands
{
	public function getResellerPackages()
	{
		return $this->parse($this->guzzle->get('/CMD_API_PACKAGES_RESELLER'));
	}

	public function getResellerPackage($package)
	{
		return $this->parse($this->guzzle->get('/CMD_API_PACKAGES_RESELLER', ['form_params' => [
			'package' => $package
		]]));
	}

	public function getUserPackages()
	{
		return $this->parse($this->guzzle->get('/CMD_API_PACKAGES_USER'));
	}

	public function getUserPackage($package)
	{
		return $this->parse($this->guzzle->get('/CMD_API_PACKAGES_USER', ['form_params' => [
			'package' => $package
		]]));
	}
}
