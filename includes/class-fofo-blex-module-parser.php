<?php

namespace FoFo_Blex;

class Fofo_Blex_Module_Parser {

	private $_raw;
	private $_element_processors;
	private $_parent_node_type;

	private $_structure = [];
	private $_current_path = '/';
	private $_processing_level = 0;

	private $_module;

	public function __construct( $file_content ) {

		$this->_raw = $file_content;
		$this->_module = new FoFo_Blex_Module_Structure();
		$this->build_element_processors();
	}

	private function build_element_processors() {

		$this->_element_processors[ 'ExpressionStatement' ] = [ $this,  'process_expression_statement' ];
		$this->_element_processors[ 'CallExpression' ] = [ $this,  'process_call_expression' ];
		$this->_element_processors[ 'Literal' ] = [ $this,  'process_literal' ];
		$this->_element_processors[ 'ObjectExpression' ] = [ $this,  'process_object_expression' ];
	}

	public function parse() {

		$peast_options = [
			'sourceType' => \Peast\Peast::SOURCE_TYPE_MODULE,
			'comments' => true,
			'jsx' => true
		];

		$ast = \Peast\Peast::latest($this->_raw, $peast_options)->parse();
		$this->process_element( $ast, '', 0 );

		return $this->_module;
	}

	private function process_element( $node, $parent_type, $processing_level ) {

		$this->_parent_node_type = $parent_type;
		$this->_processing_level = $processing_level;
		$node->traverse(function( $child_node ){

			$this->_processing_level++;
			$node_type = $child_node->getType();

			if( $node_type !== $this->_parent_node_type && isset( $this->_element_processors[ $node_type ] ) ) {

				$this->_element_processors[ $node_type ]( $child_node );				
			}

			if($this->_processing_level > 1) {

				return \Peast\Traverser::DONT_TRAVERSE_CHILD_NODES;
			}
		});
	} 

	private function process_expression_statement( $node ) {

		$node_type = $node->getType();
		$this->process_element( $node, $node_type, 0 );
	}

	private function process_call_expression( $node ) {

		$name = $node->getCallee()->getName();

		$this->_module->navigate_to( $this->_module->current_path().$name );
		$call_ex_path = $this->_module->current_path();

		$arguments = $node->getArguments();
		$arg_count = 0;
		foreach( $arguments as $argument ) {

			$this->_module->navigate_to( $call_ex_path.'arguments/'.$arg_count );
			$this->process_element( $argument, $node->getType(), 0 );
			$arg_count++;
		}
	}

	private function process_literal( $node ) {

		$this->_module->add_element( $node->getRaw() );
	}

	private function process_object_expression( $node ) {

		$this->_module->add_element( $node->getType() );
	}
}
