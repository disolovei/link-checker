<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/simple_html_dom.php';

define( 'ABSPATH', __DIR__ . DIRECTORY_SEPARATOR );

/*echo '<pre>';
    print_r( LinkChecker\Test::test() );
echo '</pre>';*/

$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	if ( $action ) {
		if ( 'scan' === $action ) {
			LinkChecker\Scaner::scan();
		} else if ( 'clean' === $action ) {
			LinkChecker\Scaner::clean();
		} else if ( 'report' === $action ) {
			LinkChecker\Scaner::generate_report();
		}
	}

	die( json_encode([
		'error' => 'Not allowed!',
	]) );
}

LinkChecker\Template::output();