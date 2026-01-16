<?php
/**
 * Resize plugin banner and icon to WordPress.org standard sizes
 * - Icon: 32x32, 64x64, 1024x1024 (source)
 * - Banner: 3:1 ratio (594x198 for dashboard widget)
 */

$imagesDir = __DIR__;
$sourceIcon = $imagesDir . '/icon-1024x1024.png';
$sourceBanner = $imagesDir . '/banner-1904x640.png';

// Define target sizes
$iconSizes = [
	[32, 32, 'icon-32x32.png'],
	[64, 64, 'icon-64x64.png'],
];

$bannerSizes = [
	[594, 198, 'banner-594x198.png'],
];

/**
 * Resize image using GD Library
 */
function resizeImage($source, $destination, $width, $height) {
	if (!file_exists($source)) {
		echo "❌ Source file not found: $source\n";
		return false;
	}

	// Get image info
	$imageType = exif_imagetype($source);
	
	if ($imageType === IMAGETYPE_PNG) {
		$sourceImage = imagecreatefrompng($source);
		$imageFunction = 'imagepng';
	} elseif ($imageType === IMAGETYPE_JPEG) {
		$sourceImage = imagecreatefromjpeg($source);
		$imageFunction = 'imagejpeg';
	} else {
		echo "❌ Unsupported image type: $source\n";
		return false;
	}

	if (!$sourceImage) {
		echo "❌ Failed to load image: $source\n";
		return false;
	}

	// Get original dimensions
	$origWidth = imagesx($sourceImage);
	$origHeight = imagesy($sourceImage);

	// Create blank canvas for resized image
	$resizedImage = imagecreatetruecolor($width, $height);
	
	// Preserve transparency for PNG
	if ($imageType === IMAGETYPE_PNG) {
		imagealphablending($resizedImage, false);
		imagesavealpha($resizedImage, true);
		$trans = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
		imagefill($resizedImage, 0, 0, $trans);
	}

	// Resize with resampling
	$result = imagecopyresampled(
		$resizedImage,
		$sourceImage,
		0, 0, 0, 0,
		$width,
		$height,
		$origWidth,
		$origHeight
	);

	if (!$result) {
		echo "❌ Failed to resample image\n";
		imagedestroy($sourceImage);
		imagedestroy($resizedImage);
		return false;
	}

	// Save resized image
	if ($imageFunction === 'imagepng') {
		$saveResult = imagepng($resizedImage, $destination, 9);
	} else {
		$saveResult = imagejpeg($resizedImage, $destination, 95);
	}

	imagedestroy($sourceImage);
	imagedestroy($resizedImage);

	if ($saveResult) {
		$size = filesize($destination) / 1024;
		echo "✅ Created: $destination (" . number_format($size, 1) . " KB)\n";
		return true;
	} else {
		echo "❌ Failed to save: $destination\n";
		return false;
	}
}

// Resize icon
echo "\n📦 RESIZING ICON\n";
echo "Source: $sourceIcon (" . getimagesize($sourceIcon)[0] . "x" . getimagesize($sourceIcon)[1] . ")\n\n";

foreach ($iconSizes as [$width, $height, $filename]) {
	$destination = $imagesDir . '/' . $filename;
	resizeImage($sourceIcon, $destination, $width, $height);
}

// Resize banner
echo "\n📦 RESIZING BANNER\n";
echo "Source: $sourceBanner (" . getimagesize($sourceBanner)[0] . "x" . getimagesize($sourceBanner)[1] . ")\n";
echo "Target ratio: 3:1 (594x198 for 33% dashboard)\n\n";

foreach ($bannerSizes as [$width, $height, $filename]) {
	$destination = $imagesDir . '/' . $filename;
	resizeImage($sourceBanner, $destination, $width, $height);
}

echo "\n✅ Image resizing complete!\n";
echo "Files ready for WordPress.org plugin page.\n";
