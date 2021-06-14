<?php
namespace LinkChecker;

class Test {
	public static function test() {
		try {
			$chromeOptions = new \Facebook\WebDriver\Chrome\ChromeOptions();
			$chromeOptions->setBinary( ABSPATH . 'chromedriver');
			$chromeOptions->addArguments(['--headless', "--no-sandbox", "--log-level=INFO", "--disable-gpu", '--disable-extensions', '--privileged', '--remote-debugging-port=9222']);

			$capabilities = \Facebook\WebDriver\Remote\DesiredCapabilities::chrome();
			$capabilities->setPlatform("Linux");
			$capabilities->setCapability(\Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $chromeOptions);

			$driver = \Facebook\WebDriver\Remote\RemoteWebDriver::create('http://localhost:3333/', $capabilities);

			$html = $driver->get('http://dev.loc');

			$driver->quit();

			return $html;
		} catch( \Facebook\WebDriver\Exception\WebDriverCurlException $e ) {
			echo $e->getMessage();
		}
	}
}