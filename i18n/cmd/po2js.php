<?php

namespace cmd\i18n;


class po2js extends po2php
{
	const desc = "Create js translation from po";
	const assign = ": ";


	protected function escape($str)
	{
		return str_replace(["\n", "\r"], ["\\n", "\\r"], parent::escape($str));
	}


	protected function print($file, $plural, $msgs)
	{
		return <<<EOT
window.i18nMessages = {
	msgs: {{$msgs}
	},
	plural(n)
	{
		return $plural;
	}
};

EOT;
	}
}
