<?php
namespace LinkChecker;

class Scaner {
	public static function clean() {
		$reports = glob( ABSPATH . 'report-*.csv' );

		if ( count( $reports ) > 0 ) {
			foreach ( $reports as $file ) {
				unlink( $file );
			}
		}

		self::end([]);
	}

	public static function generate_report() {
		if ( empty( $_POST['report'] ) || ! is_array( $_POST['report'] ) ) {
			self::end( [
				'error' => 'Empty data!'
			] );
		}

		$filename   = 'report-' . date( 'Y-m-d' ) . time() . '.csv';
		$file       = fopen( ABSPATH . $filename, 'w' );

		if ( false === $file ) {
			self::end([
				'error' => 'Permission denied!'
			]);
		}

		fputcsv( $file, ['Donor', 'Acceptor', 'Link text', 'Rel', 'Robots'] );

		foreach ( $_POST['report'] as $row ) {
			fputcsv( $file, $row );
		}

		fclose($file);

		self::end( ['file' => $filename] );
	}

  public static function scan() {
    $donor      = filter_input( INPUT_POST, 'donor', FILTER_SANITIZE_STRING );
    $acceptor   = ! empty( $_POST['acceptor'] ) ? @explode( ',', $_POST['acceptor'] ) : [];

    if ( ! $acceptor || ! $donor ) {
      self::end( [
	      'error' => 'Missing args!'
      ] );
    }

    $acceptor = array_map( 'rawurldecode', $acceptor );

    $page = self::load_page($donor);

    $html = @str_get_html($page);

	  $temp = [
		  'text'      => '',
		  'rel'       => '',
		  'found'     => 'Not found',
		  'donor'     => $donor,
		  'robots'    => '',
	  ];

    if ( ! $html ) {
    	self::end( $temp );
    }

    $links = @$html->find('a');

    if ( ! $links ) {
	    self::end( $temp );
    }

    $meta_robots = @$html->find('meta[robots]');
	$pattern = self::get_search_pattern( $acceptor );

	if ( ! $pattern ) {
		self::end([
			'error' => 'Something error!',
		]);
	}

    foreach ( $links as $link ) {
    	$href = @$link->href;

    	if ( preg_match($pattern, $href) ) {
		    $temp['text']   = strip_tags( (string)$link->innertext );
		    $temp['rel']    = (string)@$link->getAttribute('rel');
		    $temp['found']  = $href;
		    $temp['donor']  = $donor;

		    if ( $meta_robots ) {
			    $temp['robots'] = (string)@$meta_robots->attr['content'];
		    } else {
			    $temp['robots'] = 'index/follow';
		    }

		    break;
	    }
    }

    self::end( $temp );
  }

  protected static function load_page($link) {
	  $ch = curl_init($link);

	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	  curl_setopt($ch, CURLOPT_HEADER, 0);
	  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
	  $html = curl_exec($ch);

	  curl_close($ch);

	  return (string)$html;
  }

  protected static function end( $response = [] ) {
	  header('Content-Type: application/json');
	  echo @json_encode( (array)$response );
	  die();
  }

  protected static function get_search_pattern( $acceptor ) {
		if ( ! is_array( $acceptor ) || count( $acceptor ) === 0 ) {
			return '';
		}

		if ( count( $acceptor ) === 1 ) {
			return '~(' . preg_quote( $acceptor[0] ) . '|' . preg_quote( urlencode( $acceptor[0] ) ) . ')~i';
		} else {
			return '~^' . join( '|', $acceptor ) . ')~';
		}
  }
}