<?php

namespace daisywheel\core;

class Config
{
	protected $env = '';
	protected $entries = array();

	public function __construct($entries, $env='')
	{
		$this->entries = $entries;
		$this->env = $env;
	}

	public function getEnv()
	{
		return $this->env;
	}

	public function has($path)
	{
		$node = $this->entries;

		foreach (explode('/', $path) as $subPath) {
			if (!is_array($node) || !array_key_exists($subPath, $node)) {
				return false;
			}

			$node = $node[$subPath];
		}

		return true;
	}

	public function get($path, $default=null)
	{
		$node = $this->entries;

		foreach (explode('/', $path) as $subPath) {
			if (!is_array($node) || !array_key_exists($subPath, $node)) {
				return $default;
			}

			$node = $node[$subPath];
		}

		return $node;
	}

	public function slice($path)
	{
		$subEntries = $this->get($path, array());

		if (is_array($subEntries)) {
			return new self($subEntries, $this->env);
		}

		return new self(array(), $this->env);
	}

	public function remove($what)
	{
		if (is_string($what)) {
			$what = array($what);
		}

		foreach ($what as $key) {
			if (array_key_exists($key, $this->entries)) {
				unset($this->entries[$key]);
			}
		}

		return $this;
	}

	public function defaults($entries)
	{
		if (!is_array($entries)) {
			$entries = $entries->entries;
		}

		$this->entries = ArrayHelper::merge($entries, $this->entries);
		return $this;
	}

	public function merge($entries)
	{
		if (!is_array($entries)) {
			$entries = $entries->entries;
		}

		$this->entries = ArrayHelper::merge($this->entries, $entries);
		return $this;
	}

	public static function create($configPath, $configType)
	{
		if (is_readable("{$configPath}/env")) {
			$env = trim(file_get_contents("{$configPath}/env"));

			if (is_readable("{$configPath}/{$configType}.{$env}.php")) {
				$entries = require("{$configPath}/{$configType}.{$env}.php");
				return new self($entries, $env);
			}
		}

		$entries = require("{$configPath}/{$configType}.php");
		return new self($entries, '');
	}
}
