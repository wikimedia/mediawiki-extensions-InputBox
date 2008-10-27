<?php
/**
 * Classes for InputBox extension
 *
 * @file
 * @ingroup Extensions
 */

// InputBox class
class InputBox {

	/* Fields */
	
	private $_parser;
	private $_type;
	private $_width;
	private $_preload;
	private $_editintro;
	private $_br;
	private $_defaulttext;
	private $_bgcolor;
	private $_buttonlabel;
	private $_searchbuttonlabel;
	private $_labeltext;
	private $_hidden;
	private $_namespaces;
	private $_id;
	private $_inline;
	
	/* Functions */
	
	public function __construct( $parser ) {
		$this->_parser = $parser;
	}
	
	public function render() {
		// Internationalization
		wfLoadExtensionMessages( 'InputBox' );
		
		// Handle various types
		switch( $this->_type ) {
			case 'create':
			case 'comment':
				return $this->getCreateForm();
			case 'search':
				return $this->getSearchForm();
			case 'search2':
				return $this->getSearchForm2();
			default:
				return Xml::tags( 'div', null,
					Xml::element( 'strong',
						array(
							'class' => 'error'
						),
						strlen( $this->_type ) > 0
						? htmlspecialchars( wfMsgForContent( 'inputbox-error-bad-type', $this->_type ) )
						: htmlspecialchars( wfMsgForContent( 'inputbox-error-no-type' ) )
					)
				);
		}
	}

	/**
	 * Generate search form
	 */
	public function getSearchForm() {
		global $wgContLang;
		
		// Use button label fallbacks
		if( !$this->_buttonlabel ) {
			$this->_buttonlabel = wfMsgHtml( 'tryexact' );
		}
		if( !$this->_searchbuttonlabel ) {
			$this->_searchbuttonlabel = wfMsgHtml( 'searchfulltext' );
		}
		
		// Build HTML
		$htmlOut = Xml::openElement( 'div',
			array(
				'align' => 'center',
				'style' => 'background-color:' . $this->_bgcolor
			)
		);
		$htmlOut .= Xml::openElement( 'form',
			array(
				'name' => 'searchbox',
				'id' => 'searchbox',
				'class' => 'searchbox',
				'action' => SpecialPage::getTitleFor( 'Search' )->escapeLocalUrl(),
			)
		);
		$htmlOut .= Xml::element( 'input',
			array(
				'class' => 'searchboxInput',
				'name' => 'search',
				'type' => $this->_hidden ? 'hidden' : 'text',
				'value' => $this->_defaulttext,
				'size' => $this->_width,
			)
		);
		$htmlOut .= $this->_br;

		// Determine namespace checkboxes
		$namespaces = $wgContLang->getNamespaces();
		$namespacesArray = explode( ',', $this->_namespaces );
		if ( $this->_namespaces ) {
			foreach ( $namespacesArray as $userNamespace ) {
				$checked = array();
				// Namespace needs to be checked if flagged with "**" or if it's the only one
				if ( strstr( $userNamespace, '**' ) || count( $namespacesArray ) == 1 ) {
					$userNamespace = str_replace( '**', '', $userNamespace );
					$checked = array( 'checked' => 'checked' );
				}
				
				// Namespace checkboxes
				foreach ( $namespaces as $i => $name ) {
					if ( $i < 0 ) {
						continue;
					} elseif ( $i == 0 ) {
						$name = 'Main';
					}
					if ( $userNamespace == $name) {
						// Checkbox
						$htmlOut .= Xml::element( 'input',
							array(
								'type' => 'checkbox',
								'name' => 'ns' . $i,
								'value' => 1
							) + $checked
						);
						// Label
						$htmlOut .= '&nbsp;' . htmlspecialchars( $userNamespace );
					}
				}
			}
			
			// Line break 
			$htmlOut .= $this->_br;
		} else {
			// Go button
			$htmlOut .= Xml::element( 'input',
				array(
					'type' => 'submit',
					'name' => 'go',
					'class' => 'searchboxGoButton',
					'value' => $this->_buttonlabel
				)
			);
			$htmlOut .= '&nbsp;';
		}
		
		// Search button
		$htmlOut .= Xml::element( 'input',
			array(
				'type' => 'submit',
				'name' => 'fulltext',
				'class' => 'searchboxSearchButton',
				'value' => $this->_searchbuttonlabel
			)
		);
		$htmlOut .= Xml::closeElement( 'form' );
		$htmlOut .= Xml::closeElement( 'div' );
		
		// Return HTML
		return $htmlOut;
	}
	
	/*
	 * Generate search form version 2
	 */
	public function getSearchForm2() {
		
		// Use button label fallbacks
		if( !$this->_buttonlabel ) {
			$this->_buttonlabel = wfMsgHtml( 'tryexact' );
		}
		
		$id = Sanitizer::escapeId( $this->_id );
		$htmlLabel = '';
		if ( isset( $this->_labeltext ) && strlen( trim( $this->_labeltext ) ) ) {
			$output = $this->_parser->parse(
				$this->_labeltext,
				$this->_parser->getTitle(),
				$this->_parser->getOptions(),
				false,
				false
			);
			$this->_labeltext = $output->getText();
			$this->_labeltext = str_replace( '<p>', '', $this->_labeltext );
			$this->_labeltext = str_replace( '</p>', '', $this->_labeltext );
			$htmlLabel = Xml::element( 'label',
				array(
					'for' => 'bodySearchIput' . $id
				),
				$this->_labeltext
			);
		}
		
		$htmlOut = Xml::openElement( 'form',
			array(
				'name' => 'bodySearch' . $id,
				'id' => 'bodySearch' . $id,
				'class' => 'bodySearch',
				'action' => SpecialPage::getTitleFor( 'Search' )->escapeLocalUrl(),
				'style' => $this->_inline ? 'display: inline;' : ''
			)
		);
		$htmlOut .= Xml::openElement( 'div',
			array(
				'class' => 'bodySearchWrap',
				'style' => 'background-color:' . $this->_bgcolor . ';' .
					$this->_inline ? 'display: inline;' : ''
			)
		);
		$htmlOut .= $htmlLabel;
		$htmlOut .= Xml::element( 'input',
			array(
				'type' => $this->_hidden ? 'hidden' : 'text',
				'name' => 'search',
				'size' => $this->_width,
				'class' => 'bodySearchInput' . $id
			)
		);
		$htmlOut .= Xml::element( 'input',
			array(
				'type' => 'submit',
				'name' => 'go',
				'value' => $this->_buttonlabel,
				'class' => 'bodySearchBtnGo' . $id
			)
		);

		// Better testing needed here!
		if ( !empty( $this->_fulltextbtn ) ) {
			$htmlOut .= Xml::element( 'input',
				array(
					'type' => 'submit',
					'name' => 'fulltext',
					'class' => 'bodySearchBtnSearch',
					'value' => $this->_searchbuttonlabel
				)
			);
		}
		
		$htmlOut .= Xml::closeElement( 'div' );
		$htmlOut .= Xml::closeElement( 'form' );

		// Return HTML
		return $htmlOut;
	}

	/**
	 * Generate create page form
	 */
	public function getCreateForm() {
		global $wgScript;	

		if($this->_type=="comment") {
			if(!$this->_buttonlabel) {
				$this->_buttonlabel = wfMsgHtml( "postcomment" );
			}
		} else {
			$comment='';
			if(!$this->_buttonlabel) {			
				$this->_buttonlabel = wfMsgHtml( 'createarticle' );
			}
		}
		
		$htmlOut = Xml::openElement( 'div',
			array(
				'align' => 'center',
				'style' => 'background-color:' . $this->_bgcolor
			)
		);
		$htmlOut .= Xml::openElement( 'form',
			array(
				'name' => 'createbox',
				'id' => 'createbox',
				'class' => 'createbox',
				'action' => $wgScript,
				'method' => 'get'
			)
		);
		$htmlOut .= Xml::openElement( 'input',
			array(
				'type' => 'hidden',
				'name' => 'action',
				'value' => 'edit',
			)
		);
		$htmlOut .= Xml::openElement( 'input',
			array(
				'type' => 'hidden',
				'name' => 'preload',
				'value' => $this->_preload,
			)
		);
		$htmlOut .= Xml::openElement( 'input',
			array(
				'type' => 'hidden',
				'name' => 'editintro',
				'value' => $this->_editintro,
			)
		);
		if( $this->_type == 'comment' ) {
			$htmlOut .= Xml::openElement( 'input',
				array(
					'type' => 'hidden',
					'name' => 'section',
					'value' => 'new',
				)
			);
		}
		$htmlOut .= Xml::openElement( 'input',
			array(
				'type' => $this->_hidden ? 'hidden' : 'text',
				'name' => 'title',
				'class' => 'createboxInput',
				'value' => $this->_defaulttext,
				'size' => $this->_width
			)
		);
		$htmlOut .= $this->_br;
		$htmlOut .= Xml::openElement( 'input',
			array(
				'type' => 'submit',
				'name' => 'create',
				'class' => 'createboxButton',
				'value' => $this->_buttonlabel
			)
		);
		$htmlOut .= Xml::closeElement( 'form' );
		$htmlOut .= Xml::closeElement( 'div' );
		
		// Return HTML
		return $htmlOut;
	}

	/**
	 * Extract options from a blob of text
	 *
	 * @param string $text Tag contents
	 */
	public function extractOptions( $text ) {
		wfProfileIn( __METHOD__ );
		
		// Parse all possible options
		$values = array();
		foreach( explode( "\n", $text ) as $line ) {
			if( strpos( $line, '=' ) === false )
				continue;
			list( $name, $value ) = explode( '=', $line, 2 );
			$values[ strtolower( trim( $name ) ) ] = trim( $value );
		}
		
		// Go through and set all the options we found
		$options = array(
			'type' => '_type',
			'width' => '_width',
			'preload' => '_preload',
			'editintro' => '_editintro',
			'default' => '_defaulttext',
			'bgcolor' => '_bgcolor',
			'buttonlabel' => '_buttonlabel',
			'searchbuttonlabel' => '_searchbuttonlabel',
			'namespaces' => '_namespaces',
			'id' => '_id',
			'labeltext' => '_labeltext',
			'break' => '_br',
			'hidden' => '_hidden',
			'inline' => '_inline',
		);
		foreach( $options as $name => $var ) {
			if( isset( $values[$name] ) ) {
				$this->$var = $values[$name];
			}
		}
		
		// Insert a line break if configured to do so
		$this->_br = ( strtolower( $this->_br ) == "no" ) ? '' : '<br />';

		// Validate the width; make sure it's a valid, positive integer
		$this->_width = intval( $this->_width <= 0 ? 50 : $this->_width );
		
		wfProfileOut( __METHOD__ );
	}

}
