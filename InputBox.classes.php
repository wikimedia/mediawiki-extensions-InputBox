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

	private $mParser;
	private $mType;
	private $mWidth;
	private $mPreload;
	private $mEditIntro;
	private $mBR;
	private $mDefaultText;
	private $mBGColor;
	private $mButtonLabel;
	private $mSearchButtonLabel;
	private $mFullTextButton;
	private $mLabelText;
	private $mHidden;
	private $mNamespaces;
	private $mID;
	private $mInline;

	/* Functions */

	public function __construct( $parser ) {
		$this->_parser = $parser;
	}

	public function render() {
		// Internationalization
		wfLoadExtensionMessages( 'InputBox' );

		// Handle various types
		switch( $this->mType ) {
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
						strlen( $this->mtype ) > 0
						? htmlspecialchars( wfMsgForContent( 'inputbox-error-bad-type', $this->mType ) )
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
		if ( !$this->mButtonLabel ) {
			$this->mButtonLabel = wfMsgHtml( 'tryexact' );
		}
		if ( !$this->mSearchButtonLabel ) {
			$this->mSearchButtonLabel = wfMsgHtml( 'searchfulltext' );
		}

		// Build HTML
		$htmlOut = Xml::openElement( 'div',
			array(
				'align' => 'center',
				'style' => 'background-color:' . $this->mBGColor
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
				'type' => $this->mHidden ? 'hidden' : 'text',
				'value' => $this->mDefaultText,
				'size' => $this->mWidth,
			)
		);
		$htmlOut .= $this->mBR;

		// Determine namespace checkboxes
		$namespaces = $wgContLang->getNamespaces();
		$namespacesArray = explode( ',', $this->mNamespaces );
		if ( $this->mNamespaces ) {
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
					if ( $userNamespace == $name ) {
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
			$htmlOut .= $this->mBR;
		} else {
			// Go button
			$htmlOut .= Xml::element( 'input',
				array(
					'type' => 'submit',
					'name' => 'go',
					'class' => 'searchboxGoButton',
					'value' => $this->mButtonLabel
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
				'value' => $this->mSearchButtonLabel
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
		if ( !$this->mButtonLabel ) {
			$this->mButtonLabel = wfMsgHtml( 'tryexact' );
		}

		$id = Sanitizer::escapeId( $this->mID );
		$htmlLabel = '';
		if ( isset( $this->mLabelText ) && strlen( trim( $this->mLabelText ) ) ) {
			$output = $this->mParser->parse(
				$this->mLabelText,
				$this->mParser->getTitle(),
				$this->mParser->getOptions(),
				false,
				false
			);
			$this->mLabelText = $output->getText();
			$this->mLabelText = str_replace( '<p>', '', $this->mLabelText );
			$this->mLabelText = str_replace( '</p>', '', $this->mLabelText );
			$htmlLabel = Xml::element( 'label',
				array(
					'for' => 'bodySearchIput' . $id
				),
				$this->mLabelText
			);
		}
		$htmlOut = Xml::openElement( 'form',
			array(
				'name' => 'bodySearch' . $id,
				'id' => 'bodySearch' . $id,
				'class' => 'bodySearch',
				'action' => SpecialPage::getTitleFor( 'Search' )->escapeLocalUrl(),
				'style' => $this->mInline ? 'display: inline;' : ''
			)
		);
		$htmlOut .= Xml::openElement( 'div',
			array(
				'class' => 'bodySearchWrap',
				'style' => 'background-color:' . $this->mBGColor . ';' .
					$this->mInline ? 'display: inline;' : ''
			)
		);
		$htmlOut .= $htmlLabel;
		$htmlOut .= Xml::element( 'input',
			array(
				'type' => $this->mHidden ? 'hidden' : 'text',
				'name' => 'search',
				'size' => $this->mWidth,
				'class' => 'bodySearchInput' . $id
			)
		);
		$htmlOut .= Xml::element( 'input',
			array(
				'type' => 'submit',
				'name' => 'go',
				'value' => $this->mButtonLabel,
				'class' => 'bodySearchBtnGo' . $id
			)
		);

		// Better testing needed here!
		if ( !empty( $this->mFullTextButton ) ) {
			$htmlOut .= Xml::element( 'input',
				array(
					'type' => 'submit',
					'name' => 'fulltext',
					'class' => 'bodySearchBtnSearch',
					'value' => $this->mSearchButtonLabel
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

		if ( $this->mType == "comment" ) {
			if ( !$this->mButtonLabel ) {
				$this->mButtonLabel = wfMsgHtml( "postcomment" );
			}
		} else {
			$comment = '';
			if ( !$this->mButtonLabel ) {
				$this->mButtonLabel = wfMsgHtml( 'createarticle' );
			}
		}

		$htmlOut = Xml::openElement( 'div',
			array(
				'align' => 'center',
				'style' => 'background-color:' . $this->mBGColor
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
				'value' => $this->mPreload,
			)
		);
		$htmlOut .= Xml::openElement( 'input',
			array(
				'type' => 'hidden',
				'name' => 'editintro',
				'value' => $this->mEditIntro,
			)
		);
		if ( $this->mType == 'comment' ) {
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
				'type' => $this->mHidden ? 'hidden' : 'text',
				'name' => 'title',
				'class' => 'createboxInput',
				'value' => $this->mDefaultText,
				'size' => $this->mWidth
			)
		);
		$htmlOut .= $this->mBR;
		$htmlOut .= Xml::openElement( 'input',
			array(
				'type' => 'submit',
				'name' => 'create',
				'class' => 'createboxButton',
				'value' => $this->mButtonLabel
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
		foreach ( explode( "\n", $text ) as $line ) {
			if ( strpos( $line, '=' ) === false )
				continue;
			list( $name, $value ) = explode( '=', $line, 2 );
			$values[ strtolower( trim( $name ) ) ] = trim( $value );
		}

		// Build list of options, with local member names and deafults
		$options = array(
			'type' => array( 'mType', '' ),
			'width' => array( 'mWidth', 50 ),
			'preload' => array( 'mPreload', '' ),
			'editintro' => array( 'mEditIntro', '' ),
			'break' => array( 'mBR', 'yes' ),
			'default' => array( 'mDefaultText', '' ),
			'bgcolor' => array( 'mBGColor', 'transparent' ),
			'buttonlabel' => array( 'mButtonLabel', '' ),
			'searchbuttonlabel' => array( 'mSearchButtonLabel', '' ),
			'fulltextbutton' => array( 'mFullTextButton', '' ),
			'namespaces' => array( '_namespaces', '' ),
			'labeltext' => array( 'mLabelText', '' ),
			'hidden' => array( 'mHidden', '' ),
			'id' => array( 'mID', '' ),
			'inline' => array( 'mInline', false )
		);
		foreach ( $options as $name => $var ) {
			if ( isset( $values[$name] ) ) {
				$this->$var[0] = $values[$name];
			} else {
				$this->$var[0] = $var[1];
			}
		}
		
		// Insert a line break if configured to do so
		$this->mBR = ( strtolower( $this->mBR ) == "no" ) ? '' : '<br />';

		// Validate the width; make sure it's a valid, positive integer
		$this->mWidth = intval( $this->mWidth <= 0 ? 50 : $this->mWidth );
		
		wfProfileOut( __METHOD__ );
	}

}
