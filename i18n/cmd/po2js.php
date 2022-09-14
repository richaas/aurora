<?php

namespace cmd\i18n;

use aurora\file\util as futl;
use aurora\i18n\util;
use Gettext\Loader\PoLoader;


class po2js extends po2php
{
	const desc = "Create js translation from po";


	protected function escape($str)
	{
		return str_replace(["\n", "\r"], ["\\n", "\\r"], parent::escape($str));
	}


	public function exec($poFile, $jsFile="php://stdout")
	{
		$loader = new PoLoader();

		$trans = @$loader->loadFile($poFile);

		$lang     = basename($poFile, ".po");
		$plurForm = $trans->getHeaders()->getPluralForm();
		$nplurals = (int)($plurForm[0] ?? 2);
		$plural   = $this->checkPlural($plurForm[1] ?? "n != 1");
		$msgs     = "";

		foreach ($trans->getTranslations() as $tr) {

			if ($tr->isDisabled() || !util::isTranslated($tr, $nplurals))
				continue;

			$id  = $this->escape($tr->getContext() === NULL ?
					     $tr->getOriginal() : $tr->getId());
			$msg = $this->escape($tr->getTranslation());

			$msgs .= "\n\t'$id': ";

			if ($tr->getPlural() !== NULL) {

				$msgs .= "['$msg'";

				foreach ($tr->getPluralTranslations() as $ptr) {

					$msg = $this->escape($ptr);

					$msgs .= ", '$msg'";
				}

				$msgs .= "],";
			}
			else {
				$msgs .= "'$msg',";
			}
		}

		@futl::file_put_contents($jsFile, <<<EOT
window.i18nMessages = {
	'': {
		'language': '$lang',
		'plural-forms': 'nplurals=$nplurals; plural=$plural;'
	},$msgs
};

EOT);
	}
}
