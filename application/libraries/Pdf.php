<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{
	function createPDF($html, $filename = '', $download = TRUE, $paper = 'A4', $orientation = 'portrait', $watermarkImagePath = '', $watermarkWidth = 200, $watermarkHeight = 200)
	{
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html);
		$dompdf->setPaper($paper, $orientation);
		$dompdf->render();

		// Add watermark if provided
		if (!empty($watermarkImagePath) && file_exists($watermarkImagePath)) {
			$canvas = $dompdf->getCanvas();
			// Adjust the position as needed (e.g., center the watermark)
			$x = ($canvas->get_width() - $watermarkWidth) / 2;
			$y = ($canvas->get_height() - $watermarkHeight) / 2;
			$canvas->image($watermarkImagePath, $x, $y, $watermarkWidth, $watermarkHeight);
		} else {
			log_message('error', 'Watermark image not found: ' . $watermarkImagePath);
		}

		if ($download)
			$dompdf->stream($filename . '.pdf', array('Attachment' => 1));
		else
			$dompdf->stream($filename . '.pdf', array('Attachment' => 0));
	}
}
