<?php

namespace aurora\util;

use aurora\file\util as futl;
use Exception;


class pdf
{
	public static function html2pdf($html)
	{
		try {
			$tmpfile = "/tmp/" . rand::string(42);

			futl::file_put_contents("$tmpfile.html", $html);

			sys::exec("xvfb-run -- wkhtmltopdf $tmpfile.html $tmpfile.pdf 2>&1");

			$pdf = futl::file_get_contents("$tmpfile.pdf");

			futl::unlink("$tmpfile.html");
			futl::unlink("$tmpfile.pdf");
		}
		catch (Exception $ex) {
			futl::unlink("$tmpfile.html");
			futl::unlink("$tmpfile.pdf");

			throw $ex;
		}

		return $pdf;
	}
}
