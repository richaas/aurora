<?php

namespace cmd\i18n;

use aurora\i18n\util;
use Exception;
use Gettext\Loader\PoLoader;


class pocheck
{
	const desc = "Check po translations against template";

	private $errc  = 0;
	private $warnc = 0;


	private function getId($tr)
	{
		$id  = $tr->getOriginal();
		$ctx = $tr->getContext();

		if ($ctx !== NULL)
			$id .= " [$ctx]";

		return $id;
	}


	private function error($file, $tr, $msg)
	{
		$id = $this->getId($tr);

		echo "$file: \x1b[31m$id\x1b[0m ($msg)\n";
		$this->errc++;
	}


	private function warn($file, $tr, $msg)
	{
		$id = $this->getId($tr);

		echo "$file: \x1b[33m$id\x1b[0m ($msg)\n";
		$this->warnc++;
	}


	private function check($ref, $def, $file)
	{
		$nplurals = (int)($def->getHeaders()->getPluralForm()[0] ?? 2);

		foreach ($ref->getTranslations() as $tr) {

			if ($tr->isDisabled())
				continue;

			$_tr = $def->find($tr->getContext(), $tr->getOriginal());

			if ($_tr === NULL || $_tr->isDisabled())
				$this->error($file, $tr, "missing");
			else if (!util::isTranslated($_tr, $nplurals))
				$this->error($file, $tr, "not translated");
			else if ($_tr->getPlural() !== $tr->getPlural())
				$this->error($file, $tr, "plural mismatch");
		}

		foreach ($def->getTranslations() as $tr) {

			if ($tr->isDisabled())
				continue;

			$_tr = $ref->find($tr->getContext(), $tr->getOriginal());

			if ($_tr === NULL || $_tr->isDisabled())
				$this->warn($file, $tr, "obsolete");
		}
	}


	public function exec($potFile, ...$poFiles)
	{
		$loader = new PoLoader();

		$ref = @$loader->loadFile($potFile);

		foreach ($poFiles as $poFile) {

			$def = @$loader->loadFile($poFile);

			$this->check($ref, $def, $poFile);
		}

		if ($this->errc > 0)
			throw new Exception("errors: $this->errc, warnings: $this->warnc");

		if ($this->warnc > 0)
			echo "warnings: $this->warnc\n";
	}
}
