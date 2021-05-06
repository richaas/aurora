<?php

namespace aurora\i18n;


class util
{
	public static function isTranslated($tr, $nplurals)
	{
		if (!$tr->isTranslated())
			return false;

		if ($tr->getPlural() === NULL)
			return true;

		$ptr = $tr->getPluralTranslations();

		for ($idx=0; $idx<$nplurals-1; $idx++)
			if (!isset($ptr[$idx]) || $ptr[$idx] === "")
				return false;

		return true;
	}
}
