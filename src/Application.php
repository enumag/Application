<?php

if (property_exists('Nette\Application\UI\PresenterComponent', 'onAnchor')) {
	require_once __DIR__ . '/Application24.php';
} else {
	require_once __DIR__ . '/Application23.php';
}
