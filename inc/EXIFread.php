<?php

class EXIFread {

	public $data, $success;

	public function __construct( $path ) {
		$this->success = false;
		$this->data    = @exif_read_data( $path, '', 1 );
		if ( $this->data ) {
			$this->success = true;
		}
	}

	public function getGPS() {
		if ( $this->success && $this->checkSectionExist( 'GPS' ) ) {
			$gps = $this->data['GPS'];
			if ( isset( $gps['GPSLatitudeRef'], $gps['GPSLongitudeRef'] ) ) {
				if ( is_array( $gps['GPSLatitude'] ) && is_array( $gps['GPSLongitude'] ) ) {
					if ( ( count( $gps['GPSLatitude'] ) == 3 ) && ( count( $gps['GPSLongitude'] ) == 3 ) ) {
						//http://en.wikipedia.org/wiki/Geographic_coordinate_conversion
						//Lalitude Calculation
						if ( strpos( $gps['GPSLatitude'][0], '/' ) === false ) {
							$degree = $gps['GPSLatitude'][0];
						} else {
							$parts  = explode( '/', $gps['GPSLatitude'][0] );
							$degree = $parts[0] / $parts[1];
						}
						if ( strpos( $gps['GPSLatitude'][1], '/' ) === false ) {
							$minutes = $gps['GPSLatitude'][1];
						} else {
							$parts   = explode( '/', $gps['GPSLatitude'][1] );
							$minutes = $parts[0] / $parts[1];
						}
						if ( strpos( $gps['GPSLatitude'][2], '/' ) === false ) {
							$seconds = $gps['GPSLatitude'][2];
						} else {
							$parts   = explode( '/', $gps['GPSLatitude'][2] );
							$seconds = $parts[0] / $parts[1];
						}
						$latitude = ( ( ( $minutes * 60 ) + $seconds ) / 3600 ) + $degree;
						if ( $gps['GPSLatitudeRef'] == 'S' ) {
							$latitude = 0 - $latitude;
						}
						//Longitude Calculation
						if ( strpos( $gps['GPSLongitude'][0], '/' ) === false ) {
							$degree = $gps['GPSLongitude'][0];
						} else {
							$parts  = explode( '/', $gps['GPSLongitude'][0] );
							$degree = $parts[0] / $parts[1];
						}
						if ( strpos( $gps['GPSLongitude'][1], '/' ) === false ) {
							$minutes = $gps['GPSLongitude'][1];
						} else {
							$parts   = explode( '/', $gps['GPSLongitude'][1] );
							$minutes = $parts[0] / $parts[1];
						}
						if ( strpos( $gps['GPSLongitude'][2], '/' ) === false ) {
							$seconds = $gps['GPSLongitude'][2];
						} else {
							$parts   = explode( '/', $gps['GPSLongitude'][2] );
							$seconds = $parts[0] / $parts[1];
						}
						$longitude = ( ( ( $minutes * 60 ) + $seconds ) / 3600 ) + $degree;
						if ( $gps['GPSLongitudeRef'] == 'W' ) {
							$longitude = 0 - $longitude;
						}

						return ( array( 'Latitude' => $latitude, 'Longitude' => $longitude ) );
					}
				}
			}

			return false;
		} else {
			return false;
		}
	}

	public function checkSectionExist( $section ) {
		if ( $this->success ) {
			if ( is_array( $this->data ) ) {
				foreach ( $this->data as $key => $value ) {
					if ( $key == $section ) {
						return true;
					}
				}
			}

			return false;
		} else {
			return false;
		}
	}

}