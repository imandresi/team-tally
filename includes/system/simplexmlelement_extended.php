<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 08/03/2023
 * Time: 09:51
 */

namespace TEAMTALLY\System;

class SimpleXMLElement_Extended extends \SimpleXMLElement {
	public function addChildWithCData( $name, $value ) {
		$child    = parent::addChild( $name );
		$element  = dom_import_simplexml( $child );
		$docOwner = $element->ownerDocument;
		$element->appendChild( $docOwner->createCDATASection( $value ) );

		return $child;
	}
}
