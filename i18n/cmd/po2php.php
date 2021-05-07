<?php

namespace cmd\i18n;

use aurora\file\util as futl;
use aurora\i18n\util;
use Exception;
use Gettext\Loader\PoLoader;


class po2php
{
	const desc = "Create php translation from po";


	protected function checkPlural($plural)
	{
		if (preg_match("/[^0-9n\s!=<>()%&|?:]/", $plural) !== 0)
			throw new Exception("invalid chars in plural formula: $plural");

		return $plural;
	}


	protected function escape($str)
	{
		return str_replace(["\\", "'", "\x00"], ["\\\\", "\\'", ""], $str);
	}


	public function exec($poFile, $phpFile="php://stdout")
	{
		$loader = new PoLoader();

		$trans = @$loader->loadFile($poFile);

		$class  = basename($phpFile, ".php");
		$plurForm = $trans->getHeaders()->getPluralForm();
		$nplurals = (int)($plurForm[0] ?? 2);
		$plural = $this->checkPlural($plurForm[1] ?? "n != 1");
		$plural = str_replace("n", "\$num", $plural);
		$msgs   = "";

		foreach ($trans->getTranslations() as $tr) {

			if ($tr->isDisabled() || !util::isTranslated($tr, $nplurals))
				continue;

			$id  = $this->escape($tr->getOriginal());
			$msg = $this->escape($tr->getTranslation());

			$msgs .= "\n\t\t'$id' => ['$msg'";

			foreach ($tr->getPluralTranslations() as $ptr) {

				$msg = $this->escape($ptr);

				$msgs .= ", '$msg'";
			}

			$msgs .= "],";
		}

		@futl::file_put_contents($phpFile, <<<EOT
<?php

namespace lang;


class $class
{
	public \$msgs = [$msgs
	];


	public function plural(\$num)
	{
		return $plural;
	}
}

EOT);
	}
}
