<?php

namespace FoFo_Blex;

class FoFo_Blex_Module_Structure {

	const PATH_DELIMETER = '/';

	private $_current_path;
	private $_structure;

	public function __construct() {

		$this->_current_path = '';
		$this->_structure = [];
	}

	public function current_path() {

		return $this->_current_path.self::PATH_DELIMETER;
	}

	public function navigate_to( $absolute_path ) {
		
		$this->_current_path = $absolute_path;
	}

	public function add_element( $element ) {

		$path_parts = explode( self::PATH_DELIMETER, $this->_current_path );
		$this->_structure = $this->build_structure( $this->_structure, $path_parts, 0, $element );
	}

	private function build_structure( $elements, $path_parts, $index, $element_to_add ) {

		$index++;
		if( $index < count( $path_parts ) ) {

			if( !isset( $elements[ $path_parts[ $index ] ] ) ) {
				
				$elements[ $path_parts[ $index ] ] = [];
			}

			$elements[ $path_parts[ $index ] ] = $this->build_structure( $elements[ $path_parts[ $index ] ], $path_parts, $index, $element_to_add );
		} else {

			$elements = $element_to_add;
		}

		return $elements;
	}

	public function get_element() {

		$path_parts = explode( self::PATH_DELIMETER, $this->_current_path );
		$path_parts = array_slice( $path_parts, 1, count( $path_parts ) );
		$pointer = $this->_structure;
		foreach( $path_parts as $path_part ) {

			$pointer = $pointer[ $path_part ];
		}

		return $pointer;
	}

	public function getStructure() {

		return $this->_structure;
	}
}
